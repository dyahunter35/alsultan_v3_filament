<?php

namespace Database\Factories;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition()
    {
        return [
            'name' => $this->faker->realTextBetween(10, 30),
            'type' => $this->faker->randomElement(['PDF', 'Word', 'Excel']),
            'note' => $this->faker->optional()->realTextBetween(10, 50),
            'file_type' => $this->faker->fileExtension(),
            'issuance_date' => $this->faker->date(),
        ];
    }

    public function forDocumentable($model)
    {
        return $this->state(function () use ($model) {
            return [
                'documentable_id' => $model->id,
                'documentable_type' => get_class($model),
            ];
        });
    }
}
