<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileIdentityFieldsTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides=[]): array
    {
        return array_merge([
            'name'=>'Budi Profil','user_type'=>'peserta','nip_nik'=>'199001012020011001',
            'whatsapp'=>'628123456789','gender'=>'Laki-Laki','birth_place'=>'Bandung',
            'birth_date'=>'1990-01-01','jabatan'=>'Analis SDM','golongan'=>'III/a',
            'instansi'=>'BPSDM Jawa Barat','status_kepegawaian'=>'PNS','provinsi'=>'JAWA BARAT',
            'kota'=>'KOTA BANDUNG','kecamatan'=>'COBLONG','kelurahan'=>'DAGO',
            'address'=>'Jl. Ir. H. Juanda No. 1, Bandung','latitude'=>'-6.8914800','longitude'=>'107.6106600',
        ],$overrides);
    }

    public function test_complete_profile_saves_identity_and_address_fields(): void
    {
        $user=User::factory()->create(['role'=>'participant','nip_nik'=>null]);

        $this->actingAs($user)->get(route('participant.profile.complete'))
            ->assertOk()->assertSee('name="birth_place"',false)->assertSee('name="birth_date"',false)
            ->assertSee('name="golongan"',false)->assertSee('name="address"',false)
            ->assertSee('id="userTypeExplanation"',false);

        $this->post(route('participant.profile.store'),$this->payload())->assertRedirect(route('participant.dashboard'));

        $user->refresh();
        $this->assertSame('Bandung',$user->birth_place);
        $this->assertSame('1990-01-01',$user->birth_date->format('Y-m-d'));
        $this->assertSame('III/a',$user->golongan);
        $this->assertSame('Jl. Ir. H. Juanda No. 1, Bandung',$user->address);
    }

    public function test_general_profile_update_saves_new_fields(): void
    {
        $user=User::factory()->create(['role'=>'superadmin','nip_nik'=>'OLD']);

        $this->actingAs($user)->put(route('profile.update'),$this->payload(['user_type'=>null,'golongan'=>'XIV','status_kepegawaian'=>'PPPK-PW']))
            ->assertRedirect()->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('Bandung',$user->birth_place);
        $this->assertSame('XIV',$user->golongan);
        $this->assertSame('PPPK-PW',$user->status_kepegawaian);
        $this->assertSame('Jl. Ir. H. Juanda No. 1, Bandung',$user->address);
        $this->assertEqualsWithDelta(-6.89148,$user->latitude,0.000001);
    }

    public function test_authenticated_user_can_search_address_through_backend_proxy(): void
    {
        \Illuminate\Support\Facades\Cache::flush();
        \Illuminate\Support\Facades\Http::fake([
            'https://nominatim.openstreetmap.org/search*'=>\Illuminate\Support\Facades\Http::response([[
                    'name'=>'Jalan Ir. H. Juanda',
                    'display_name'=>'Jalan Ir. H. Juanda, Dago, Coblong, Kota Bandung, Jawa Barat',
                    'lat'=>'-6.89148',
                    'lon'=>'107.61066',
                    'type'=>'residential',
                ]],200),
        ]);
        $user=User::factory()->create(['role'=>'participant']);

        $this->actingAs($user)->getJson(route('profile.address-search',['q'=>'Jalan Ir. H. Juanda']))
            ->assertOk()
            ->assertJsonPath('data.0.display_name','Jalan Ir. H. Juanda, Dago, Coblong, Kota Bandung, Jawa Barat')
            ->assertJsonPath('data.0.lat',-6.89148);

        \Illuminate\Support\Facades\Http::assertSent(fn($request)=>
            str_starts_with($request->url(),'https://nominatim.openstreetmap.org/search')
            && $request['countrycodes']==='id'
            && filled($request->header('User-Agent')[0]??null)
        );
    }
}
