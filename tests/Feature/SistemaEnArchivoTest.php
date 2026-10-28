<?php

namespace Tests\Feature;

use App\Models\AccionTecnica;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Copias y actualizaciones sobre una base SQLite en archivo.
 * No usa RefreshDatabase: VACUUM no puede ir dentro de una transacción.
 */
class SistemaEnArchivoTest extends TestCase
{
    private string $carpeta;

    protected function setUp(): void
    {
        parent::setUp();

        $this->carpeta = sys_get_temp_dir().DIRECTORY_SEPARATOR.'abaco-sistema-'.uniqid();
        File::ensureDirectoryExists($this->carpeta.DIRECTORY_SEPARATOR.'migraciones');
        File::ensureDirectoryExists($this->carpeta.DIRECTORY_SEPARATOR.'almacen');
        touch($this->carpeta.DIRECTORY_SEPARATOR.'base.sqlite');

        config(['database.connections.archivo' => [
            'driver' => 'sqlite',
            'database' => $this->carpeta.DIRECTORY_SEPARATOR.'base.sqlite',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]]);
        DB::setDefaultConnection('archivo');
        app()->useStoragePath($this->carpeta.DIRECTORY_SEPARATOR.'almacen');

        Artisan::call('migrate', ['--database' => 'archivo', '--force' => true]);

        $this->beforeApplicationDestroyed(function () {
            DB::disconnect('archivo');
            File::deleteDirectory($this->carpeta);
        });
    }

    public function test_la_copia_es_una_base_completa_y_queda_anotada(): void
    {
        $tecnico = User::factory()->tecnico()->create();

        $this->actingAs($tecnico)
            ->post(route('tecnico.copia'))
            ->assertSessionHas('ok');

        $copias = File::files($this->carpeta.DIRECTORY_SEPARATOR.'almacen'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'copias');
        $this->assertCount(1, $copias);

        $copia = new \PDO('sqlite:'.$copias[0]->getPathname());
        $this->assertSame(1, (int) $copia->query('select count(*) from users')->fetchColumn());

        $this->assertDatabaseHas('acciones_tecnicas', [
            'user_id' => $tecnico->id,
            'accion' => AccionTecnica::COPIA,
        ]);
    }

    public function test_exportar_la_copia_devuelve_un_archivo_sqlite_descargable(): void
    {
        $tecnico = User::factory()->tecnico()->create();

        $respuesta = $this->actingAs($tecnico)
            ->post(route('tecnico.exportar'));

        $respuesta
            ->assertOk()
            ->assertHeader('Content-Type', 'application/x-sqlite3');
        $this->assertStringContainsString(
            'attachment; filename=jornada-',
            (string) $respuesta->headers->get('Content-Disposition'),
        );
        $this->assertDatabaseHas('acciones_tecnicas', [
            'user_id' => $tecnico->id,
            'accion' => AccionTecnica::COPIA,
        ]);
    }

    public function test_actualizar_hace_la_copia_antes_de_aplicar_los_cambios(): void
    {
        $tecnico = User::factory()->tecnico()->create();
        $this->migracionNueva('2099_01_01_000000_crear_tabla_de_prueba', 'Schema::create("prueba", fn ($t) => $t->id());');

        $this->actingAs($tecnico)
            ->post(route('tecnico.actualizar'), ['confirmo' => '1'])
            ->assertSessionHas('ok');

        $this->assertTrue(Schema::hasTable('prueba'));

        $detalle = AccionTecnica::where('accion', AccionTecnica::ACTUALIZACION)->firstOrFail()->detalle;
        $this->assertStringContainsString('Aplicados: 2099_01_01_000000_crear_tabla_de_prueba', $detalle);
        $this->assertStringContainsString('Copia previa: jornada-', $detalle);

        $copias = File::files($this->carpeta.DIRECTORY_SEPARATOR.'almacen'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'copias');
        $copia = new \PDO('sqlite:'.$copias[0]->getPathname());
        $this->assertSame(0, (int) $copia->query("select count(*) from sqlite_master where name = 'prueba'")->fetchColumn());
    }

    public function test_si_la_actualizacion_falla_conserva_la_copia_y_lo_anota(): void
    {
        $tecnico = User::factory()->tecnico()->create();
        $this->migracionNueva('2099_01_01_000000_migracion_rota', 'throw new RuntimeException("rota a propósito");');

        $this->actingAs($tecnico)
            ->post(route('tecnico.actualizar'), ['confirmo' => '1'])
            ->assertSessionHas('aviso');

        $this->assertStringContainsString(
            'No se pudo actualizar',
            session('aviso'),
        );
        $this->assertCount(1, File::files($this->carpeta.DIRECTORY_SEPARATOR.'almacen'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'copias'));
        $this->assertStringContainsString(
            'Falló',
            AccionTecnica::where('accion', AccionTecnica::ACTUALIZACION)->firstOrFail()->detalle,
        );
    }

    public function test_restaurar_una_copia_guardada_deja_esa_base_y_conserva_la_anterior(): void
    {
        $tecnico = User::factory()->tecnico()->create();
        $this->actingAs($tecnico)->post(route('tecnico.copia'))->assertSessionHas('ok');

        $nombre = File::files($this->carpetaDeCopias())[0]->getFilename();
        User::factory()->create();

        $this->actingAs($tecnico)
            ->post(route('tecnico.restaurar'), [
                'copia' => $nombre,
                'confirmo' => '1',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('ok');

        $this->assertSame(1, DB::table('users')->count());
        $this->assertDatabaseHas('acciones_tecnicas', [
            'accion' => AccionTecnica::RESTAURACION,
        ]);

        $anterior = collect(File::files($this->carpetaDeCopias()))
            ->first(fn ($archivo) => $archivo->getFilename() !== $nombre);
        $this->assertNotNull($anterior);
        $copia = new \PDO('sqlite:'.$anterior->getPathname());
        $this->assertSame(2, (int) $copia->query('select count(*) from users')->fetchColumn());
    }

    public function test_restaurar_un_archivo_sqlite_sustituye_la_base(): void
    {
        $tecnico = User::factory()->tecnico()->create();
        $this->actingAs($tecnico)->post(route('tecnico.copia'))->assertSessionHas('ok');

        $subida = $this->carpeta.DIRECTORY_SEPARATOR.'traida.sqlite';
        File::copy(File::files($this->carpetaDeCopias())[0]->getPathname(), $subida);
        User::factory()->create();

        $this->actingAs($tecnico)
            ->post(route('tecnico.restaurar'), [
                'confirmo' => '1',
                'archivo' => new UploadedFile($subida, 'traida.sqlite', 'application/x-sqlite3', null, true),
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('ok');

        $this->assertSame(1, DB::table('users')->count());
    }

    public function test_un_archivo_que_no_es_sqlite_no_cambia_la_base(): void
    {
        $tecnico = User::factory()->tecnico()->create();
        User::factory()->create();
        $ruta = $this->carpeta.DIRECTORY_SEPARATOR.'no-sqlite.txt';
        file_put_contents($ruta, 'esto no es una base');

        $this->actingAs($tecnico)
            ->post(route('tecnico.restaurar'), [
                'confirmo' => '1',
                'archivo' => new UploadedFile($ruta, 'no-sqlite.sqlite', 'text/plain', null, true),
            ])
            ->assertSessionHas('aviso', 'Ese archivo no es una base SQLite.');

        $this->assertSame(2, DB::table('users')->count());
        $this->assertDatabaseMissing('acciones_tecnicas', [
            'accion' => AccionTecnica::RESTAURACION,
        ]);
    }

    public function test_restaurar_exige_confirmacion_y_una_sola_base(): void
    {
        $tecnico = User::factory()->tecnico()->create();

        $this->actingAs($tecnico)
            ->post(route('tecnico.restaurar'), ['copia' => 'jornada-20261028-120000.sqlite'])
            ->assertSessionHasErrors('confirmo');

        $this->actingAs($tecnico)
            ->post(route('tecnico.restaurar'), ['confirmo' => '1'])
            ->assertSessionHas('aviso', 'Elige la base que quieres usar.');

        $ruta = $this->carpeta.DIRECTORY_SEPARATOR.'otra.sqlite';
        file_put_contents($ruta, 'sqlite');

        $this->actingAs($tecnico)
            ->post(route('tecnico.restaurar'), [
                'confirmo' => '1',
                'copia' => 'jornada-20261028-120000.sqlite',
                'archivo' => new UploadedFile($ruta, 'otra.sqlite', 'application/x-sqlite3', null, true),
            ])
            ->assertSessionHas('aviso', 'Elige una copia guardada o un archivo, no las dos.');

        $this->actingAs($tecnico)
            ->post(route('tecnico.restaurar'), [
                'confirmo' => '1',
                'copia' => '../base.sqlite',
            ])
            ->assertSessionHas('aviso', 'El nombre de la copia no es válido.');

        $this->assertSame(1, DB::table('users')->count());
        $this->assertDatabaseMissing('acciones_tecnicas', [
            'accion' => AccionTecnica::RESTAURACION,
        ]);
    }

    private function carpetaDeCopias(): string
    {
        return $this->carpeta.DIRECTORY_SEPARATOR.'almacen'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'copias';
    }

    private function migracionNueva(string $nombre, string $codigo): void
    {
        $carpeta = $this->carpeta.DIRECTORY_SEPARATOR.'migraciones';

        file_put_contents($carpeta.DIRECTORY_SEPARATOR.$nombre.'.php', <<<PHP
        <?php

        use Illuminate\Database\Migrations\Migration;
        use Illuminate\Support\Facades\Schema;

        return new class extends Migration
        {
            public function up(): void
            {
                {$codigo}
            }
        };
        PHP);

        app('migrator')->path($carpeta);
    }
}
