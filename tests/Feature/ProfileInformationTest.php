<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Livewire\UpdateProfileInformationForm;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_profile_information_is_available(): void
    {
        $this->actingAs($user = User::factory()->create());

        $component = Livewire::test(UpdateProfileInformationForm::class);

        $this->assertEquals($user->nombres, $component->state['nombres']);
        $this->assertEquals($user->ap_paterno, $component->state['ap_paterno']);
        $this->assertEquals($user->ap_materno, $component->state['ap_materno']);
        $this->assertEquals($user->correo, $component->state['correo']);
    }

    public function test_profile_information_can_be_updated(): void
    {
        $this->actingAs($user = User::factory()->create());

        Livewire::test(UpdateProfileInformationForm::class)
            ->set('state', [
                'nombres' => 'Test',
                'ap_paterno' => 'Usuario',
                'ap_materno' => 'Prueba',
                'correo' => 'test@example.com',
            ])
            ->call('updateProfileInformation');

        $user->refresh();

        $this->assertEquals('Test', $user->nombres);
        $this->assertEquals('Usuario', $user->ap_paterno);
        $this->assertEquals('Prueba', $user->ap_materno);
        $this->assertEquals('test@example.com', $user->correo);
    }
}
