<?php

namespace Tests\Unit;

use App\Filament\Resources\FormFields\FormFieldResource;
use Tests\TestCase;

class FormFieldResourceTest extends TestCase
{
    public function test_options_are_cleaned_for_option_based_fields(): void
    {
        $data = FormFieldResource::normalizeFormData([
            'type' => 'checkbox_list',
            'options' => [' Instagram ', '', 'Google', 'Instagram'],
        ]);

        $this->assertSame(['Instagram', 'Google'], $data['options']);
    }

    public function test_options_are_cleared_for_fields_without_options(): void
    {
        $data = FormFieldResource::normalizeFormData([
            'type' => 'text',
            'options' => ['No aplica'],
        ]);

        $this->assertNull($data['options']);
    }
}
