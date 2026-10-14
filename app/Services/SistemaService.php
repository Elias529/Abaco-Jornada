<?php

namespace App\Services;

use App\Models\AccionTecnica;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Lo que necesita el administrador técnico: estado de la base de datos activa,
 * copias y actualizaciones. Solo trabaja con datos técnicos: nunca lee ni
 * devuelve el contenido de las jornadas ni los datos de las personas.
 */
class SistemaService
{
    public const COPIAS_QUE_SE_MUESTRAN = 5;

    /**
     * @return array{
     *     conexion: string,
     *     driver: string,
     *     nombre: string,
     *     responde: bool,
     *     error: ?string,
     *     milisegundos: float,
     *     bytes: ?int,
     *     integridad: ?array{correcta: bool, texto: string},
     *     tablas: list<array{nombre: string, filas: int}>,
     *     aplicadas: int,
     *     pendientes: list<string>
     * }
     */
    public function baseDeDatos(): array
    {
        $conexion = DB::connection();
        $driver = $conexion->getDriverName();
        $archivo = $this->archivoDeLaBase();

        $responde = true;
        $error = null;
        $inicio = hrtime(true);
        try {
            $conexion->select('select 1');
        } catch (Throwable $fallo) {
            $responde = false;
            $error = $fallo->getMessage();
        }
        $milisegundos = round((hrtime(true) - $inicio) / 1_000_000, 1);

        $migraciones = $responde ? $this->migraciones() : ['aplicadas' => 0, 'pendientes' => []];

        return [
            'conexion' => (string) $conexion->getName(),
            'driver' => $driver,
            'nombre' => $archivo !== null ? basename($archivo) : (string) $conexion->getDatabaseName(),
            'responde' => $responde,
            'error' => $error,
            'milisegundos' => $milisegundos,
            'bytes' => $archivo !== null ? (int) filesize($archivo) : null,
            'integridad' => $responde ? $this->integridad($driver) : null,
            'tablas' => $responde ? $this->tablas() : [],
            'aplicadas' => $migraciones['aplicadas'],
            'pendientes' => $migraciones['pendientes'],
        ];
    }

    /**
     * @return array{laravel: string, php: string, entorno: string, depuracion: bool, correo: string}
     */
    public function entorno(): array
    {
        return [
            'laravel' => app()->version(),
            'php' => PHP_VERSION,
            'entorno' => app()->environment(),
            'depuracion' => (bool) config('app.debug'),
            'correo' => (string) config('mail.default'),
        ];
    }

    /**
     * @return array{aplicadas: int, pendientes: list<string>}
     */
    public function migraciones(): array
    {
        $migrador = app('migrator');
        $repositorio = $migrador->getRepository();
        $aplicadas = $repositorio->repositoryExists() ? $repositorio->getRan() : [];
        $archivos = $migrador->getMigrationFiles([database_path('migrations'), ...$migrador->paths()]);

        return [
            'aplicadas' => count(array_intersect(array_keys($archivos), $aplicadas)),
            'pendientes' => array_values(array_diff(array_keys($archivos), $aplicadas)),
        ];
    }

    /**
     * @return list<array{nombre: string, bytes: int, fecha: string}>
     */
    public function copias(): array
    {
        if (! File::isDirectory($this->carpetaDeCopias())) {
            return [];
        }

        return collect(File::files($this->carpetaDeCopias()))
            ->sortByDesc(fn ($archivo) => $archivo->getMTime())
            ->take(self::COPIAS_QUE_SE_MUESTRAN)
            ->map(fn ($archivo) => [
                'nombre' => $archivo->getFilename(),
                'bytes' => $archivo->getSize(),
                'fecha' => date('Y-m-d H:i', $archivo->getMTime()),
            ])
            ->values()
            ->all();
    }

    /**
     * @throws RuntimeException si la base no admite copia automática
     */
    public function hacerCopia(User $tecnico): string
    {
        $nombre = $this->crearCopia();
        $this->anotar($tecnico, AccionTecnica::COPIA, $nombre);

        return $nombre;
    }

    /**
     * Aplica los cambios pendientes de la base de datos. Antes hace una copia:
     * si no puede hacerla, no toca nada.
     *
     * @throws RuntimeException si no hay copia posible o la actualización falla
     */
    public function actualizar(User $tecnico): string
    {
        $pendientes = $this->migraciones()['pendientes'];

        if ($pendientes === []) {
            $this->anotar($tecnico, AccionTecnica::ACTUALIZACION, 'Sin cambios pendientes.');

            return 'Todo está al día. No había nada que aplicar.';
        }

        $copia = $this->crearCopia();

        try {
            Artisan::call('migrate', ['--force' => true]);
        } catch (Throwable $fallo) {
            $this->anotar($tecnico, AccionTecnica::ACTUALIZACION, 'Falló: '.$fallo->getMessage().' Copia previa: '.$copia);

            throw new RuntimeException('No se pudo actualizar: '.$fallo->getMessage().' La copia previa está en '.$copia.'.');
        }

        $this->anotar(
            $tecnico,
            AccionTecnica::ACTUALIZACION,
            'Aplicados: '.implode(', ', $pendientes).'. Copia previa: '.$copia,
        );

        return 'Guardado. Se aplicaron '.count($pendientes).' cambios. Copia previa: '.$copia.'.';
    }

    private function crearCopia(): string
    {
        $archivo = $this->archivoDeLaBase();

        if ($archivo === null || DB::connection()->getDriverName() !== 'sqlite') {
            throw new RuntimeException('La copia automática solo existe para SQLite en archivo. Haz la copia desde el servidor.');
        }

        File::ensureDirectoryExists($this->carpetaDeCopias());

        $nombre = 'jornada-'.now()->format('Ymd-His').'.sqlite';
        if (File::exists($this->carpetaDeCopias().DIRECTORY_SEPARATOR.$nombre)) {
            $nombre = 'jornada-'.now()->format('Ymd-His').'-'.Str::lower(Str::random(4)).'.sqlite';
        }

        try {
            DB::connection()->statement('VACUUM INTO ?', [$this->carpetaDeCopias().DIRECTORY_SEPARATOR.$nombre]);
        } catch (Throwable $fallo) {
            throw new RuntimeException('No se pudo hacer la copia: '.$fallo->getMessage());
        }

        return $nombre;
    }

    private function carpetaDeCopias(): string
    {
        return storage_path('app'.DIRECTORY_SEPARATOR.'copias');
    }

    /**
     * Ruta del archivo de la base cuando es SQLite en disco; null en otro caso.
     */
    private function archivoDeLaBase(): ?string
    {
        $conexion = DB::connection();
        $nombre = (string) $conexion->getDatabaseName();

        if ($conexion->getDriverName() !== 'sqlite' || $nombre === '' || $nombre === ':memory:' || ! is_file($nombre)) {
            return null;
        }

        return $nombre;
    }

    /**
     * @return ?array{correcta: bool, texto: string}
     */
    private function integridad(string $driver): ?array
    {
        if ($driver !== 'sqlite') {
            return null;
        }

        $fallos = collect(DB::select('PRAGMA integrity_check'))
            ->pluck('integrity_check')
            ->reject(fn ($texto) => $texto === 'ok')
            ->values();
        $relaciones = DB::select('PRAGMA foreign_key_check');

        if ($fallos->isEmpty() && $relaciones === []) {
            return ['correcta' => true, 'texto' => 'La base está íntegra y las relaciones entre tablas cuadran.'];
        }

        $texto = $fallos->take(3)->implode(' · ');
        if ($relaciones !== []) {
            $texto .= ($texto === '' ? '' : ' · ').count($relaciones).' filas apuntan a algo que no existe';
        }

        return ['correcta' => false, 'texto' => $texto];
    }

    /**
     * Solo nombre y número de filas: nunca el contenido.
     *
     * @return list<array{nombre: string, filas: int}>
     */
    private function tablas(): array
    {
        return collect(Schema::getTables())
            ->pluck('name')
            ->reject(fn (string $nombre) => str_starts_with($nombre, 'sqlite_'))
            ->sort()
            ->map(fn (string $nombre) => ['nombre' => $nombre, 'filas' => DB::table($nombre)->count()])
            ->values()
            ->all();
    }

    private function anotar(User $tecnico, string $accion, string $detalle): void
    {
        AccionTecnica::create([
            'user_id' => $tecnico->id,
            'accion' => $accion,
            'detalle' => Str::limit($detalle, 1000),
        ]);
    }
}
