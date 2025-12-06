<?php

namespace Database\Seeders;

use App\Models\Space;
use Illuminate\Database\Seeder;

class SpaceSeeder extends Seeder
{
    public function run(): void
    {
        Space::create([
            'name' => 'Aulas o salones de clase',
            'description' => 'Espacios destinados a la enseñanza tradicional y actividades académicas.',
            'state' => true,
        ]);

        Space::create([
            'name' => 'Laboratorios',
            'description' => 'Espacios equipados para prácticas de ciencias, informática, robótica o investigación.',
            'state' => true,
        ]);

        Space::create([
            'name' => 'Biblioteca o Sala de lectura',
            'description' => 'Área para la consulta de libros, investigación y estudio individual o grupal.',
            'state' => true,
        ]);

        Space::create([
            'name' => 'Salas de reuniones',
            'description' => 'Espacios para juntas, conferencias y trabajo colaborativo.',
            'state' => true,
        ]);

        Space::create([
            'name' => 'Oficinas administrativas',
            'description' => 'Áreas destinadas a la gestión académica, administrativa y financiera.',
            'state' => true,
        ]);

        Space::create([
            'name' => 'Espacios de coworking',
            'description' => 'Zonas compartidas para estudiantes, investigadores y profesionales.',
            'state' => true,
        ]);

        Space::create([
            'name' => 'Centros de innovación',
            'description' => 'Espacios con impresoras 3D, herramientas y tecnología para proyectos innovadores.',
            'state' => true,
        ]);

        Space::create([
            'name' => 'Aulas virtuales o cabinas multimedia',
            'description' => 'Salas equipadas para grabación de clases y creación de contenido digital.',
            'state' => true,
        ]);

        Space::create([
            'name' => 'Salas de descanso o lounge',
            'description' => 'Áreas de socialización, relajación y pausas activas.',
            'state' => true,
        ]);

        Space::create([
            'name' => 'Cafeterías o comedores',
            'description' => 'Espacios destinados a la alimentación y convivencia.',
            'state' => true,
        ]);

        Space::create([
            'name' => 'Salas de asesoría académica',
            'description' => 'Espacios para la atención personalizada y orientación a estudiantes.',
            'state' => true,
        ]);

        Space::create([
            'name' => 'Auditorios o salas de conferencias',
            'description' => 'Grandes espacios para charlas, congresos y presentaciones.',
            'state' => true,
        ]);

        Space::create([
            'name' => 'Áreas deportivas y recreativas',
            'description' => 'Zonas para la práctica de deportes y actividades de esparcimiento.',
            'state' => true,
        ]);

        Space::create([
            'name' => 'Centros de investigación',
            'description' => 'Espacios especializados para investigación avanzada y desarrollo académico.',
            'state' => true,
        ]);

        // Space::factory(10000)->create();
    }
}
