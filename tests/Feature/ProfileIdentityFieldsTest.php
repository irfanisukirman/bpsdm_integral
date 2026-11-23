<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\InstitutionMunicipality;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileIdentityFieldsTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides=[]): array
    {
        $institution=\App\Models\Institution::firstOrCreate(['name'=>'BPSDM Jawa Barat'],['is_active'=>true]);
        $status=\App\Models\EmploymentStatus::where('code',$overrides['status_kepegawaian']??'PNS')->firstOrFail();
        return array_merge([
            'institution_id'=>$institution->id,'employment_status_id'=>$status->id,
            'name'=>'Budi Profil','user_type'=>'peserta','nip'=>'199001012020011001','nik'=>'3201010101900001',
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
            ->assertDontSee('name="user_type"',false)->assertSee('data-go-step="3"',false);

        $this->post(route('participant.profile.store'),$this->payload())->assertRedirect(route('participant.dashboard'));

        $user->refresh();
        $this->assertSame('199001012020011001', $user->nip);
        $this->assertSame('3201010101900001', $user->nik);
        $this->assertSame($user->nip, $user->nip_nik);
        $this->assertSame('province', $user->institution_scope);
        $this->assertSame('Bandung',$user->birth_place);
        $this->assertSame('1990-01-01',$user->birth_date->format('Y-m-d'));
        $this->assertSame('III/a',$user->golongan);
        $this->assertSame('Jl. Ir. H. Juanda No. 1, Bandung',$user->address);
    }

    public function test_profile_saves_manual_institutions_and_clears_unrelated_master_ids(): void
    {
        $user = User::factory()->create(['role' => 'participant']);
        $municipality = InstitutionMunicipality::where('name', 'Kabupaten Bandung')->firstOrFail();
        $payload = $this->payload([
            'institution_scope' => 'municipality',
            'institution_municipality_id' => $municipality->id,
            'institution_name' => 'Dinas Pendidikan Kabupaten Bandung',
        ]);

        $this->actingAs($user)->get(route('participant.profile.complete'))
            ->assertOk()->assertSee('Pemerintah Kabupaten/Kota Jawa Barat')->assertSee($municipality->name);
        $this->post(route('participant.profile.store'), array_replace($payload, ['institution_name' => '']))
            ->assertSessionHasErrors('institution_name');
        $this->post(route('participant.profile.store'), $payload)
            ->assertSessionHasNoErrors()->assertRedirect(route('participant.dashboard'));

        $user->refresh();
        $this->assertSame('municipality', $user->institution_scope);
        $this->assertEquals($municipality->id, $user->institution_municipality_id);
        $this->assertSame('Dinas Pendidikan Kabupaten Bandung', $user->instansi);
        $this->assertNull($user->institution_id);
        $this->assertSame('KOTA BANDUNG', $user->kota);

        $this->put(route('profile.update'), array_replace($payload, [
            'institution_scope' => 'other',
            'institution_name' => 'Universitas Contoh',
        ]))->assertSessionHasNoErrors()->assertSessionHas('profile_saved');
        $user->refresh();
        $this->assertSame('other', $user->institution_scope);
        $this->assertSame('Universitas Contoh', $user->instansi);
        $this->assertNull($user->institution_municipality_id);
    }

    public function test_superadmin_manages_municipalities_and_inactive_options_are_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($admin)->get(route('workforce-masters.index'))->assertOk()->assertSee('Kabupaten/Kota Jawa Barat');
        $this->post(route('workforce-masters.municipalities.store'), ['name' => 'Kota Uji'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('workforce-masters.index', ['tab' => 'kabupaten-kota']));
        $municipality = InstitutionMunicipality::where('name', 'Kota Uji')->firstOrFail();
        $this->put(route('workforce-masters.municipalities.update', $municipality), [
            'name' => 'Kota Uji Baru',
            'is_active' => false,
        ])->assertSessionHasNoErrors()->assertRedirect(route('workforce-masters.index', ['tab' => 'kabupaten-kota']));
        $this->assertFalse($municipality->fresh()->is_active);

        $participant = User::factory()->create(['role' => 'participant']);
        $this->actingAs($participant)->post(route('participant.profile.store'), $this->payload([
            'institution_scope' => 'municipality',
            'institution_municipality_id' => $municipality->id,
            'institution_name' => 'Instansi Uji',
        ]))->assertSessionHasErrors('institution_municipality_id');
    }

    public function test_workforce_master_saves_institution_and_status_on_their_respective_tabs(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'superadmin']));

        $institutions = route('workforce-masters.index', ['tab' => 'instansi']);
        $this->get($institutions)->assertOk()->assertSee('Daftar Perangkat Daerah/Instansi')
            ->assertDontSee('Kabupaten/Kota Jawa Barat');
        $this->post(route('workforce-masters.institutions.store'), ['name' => 'Instansi Uji'])
            ->assertSessionHasNoErrors()->assertRedirect($institutions);

        $statuses = route('workforce-masters.index', ['tab' => 'status-kepegawaian']);
        $this->get($statuses)->assertOk()->assertSee('Tambah Status Kepegawaian')
            ->assertDontSee('Daftar Perangkat Daerah/Instansi');
        $this->post(route('workforce-masters.statuses.store'), ['name' => 'Status Uji', 'code' => 'UJI'])
            ->assertSessionHasNoErrors()->assertRedirect($statuses);
    }

    public function test_public_registration_ignores_requested_role_and_defaults_to_participant(): void
    {
        foreach ([null, 'narasumber', 'mitra', 'superadmin'] as $requestedType) {
            $user = User::factory()->create(['role' => 'participant', 'user_type' => null]);
            $payload = $this->payload(['nip' => str_pad((string)$user->id, 18, '1', STR_PAD_LEFT), 'nik' => str_pad((string)$user->id, 16, '2', STR_PAD_LEFT)]);
            unset($payload['user_type']);
            if ($requestedType !== null) $payload['user_type'] = $requestedType;
            $this->actingAs($user)->post(route('participant.profile.store'), $payload)
                ->assertSessionHasNoErrors()->assertRedirect(route('participant.dashboard'));
            $this->assertSame('participant', $user->fresh()->role);
            $this->assertSame('peserta', $user->fresh()->user_type);
            $this->assertSame('approved', $user->fresh()->user_type_status);
        }
    }

    public function test_completion_requires_both_identifiers_with_valid_lengths_and_unique_values(): void
    {
        $user = User::factory()->create(['role' => 'participant']);
        $this->actingAs($user);
        foreach ([['nip'=>''], ['nik'=>''], ['nip'=>'123'], ['nik'=>'123'], ['nip'=>'abcdefghijklmnopqr']] as $invalid) {
            $this->post(route('participant.profile.store'), $this->payload($invalid))
                ->assertSessionHasErrors(array_keys($invalid));
        }
        User::factory()->create(['nip'=>'199001012020011001', 'nik'=>'3201010101900001']);
        $this->post(route('participant.profile.store'), $this->payload())->assertSessionHasErrors(['nip', 'nik']);
        $this->assertNull($user->fresh()->nip);
        $this->assertNull($user->fresh()->nik);
    }

    public function test_imported_participant_can_search_village_while_completing_profile(): void
    {
        \Illuminate\Support\Facades\Cache::flush();
        \Illuminate\Support\Facades\Http::fake(['https://nominatim.openstreetmap.org/search*' => \Illuminate\Support\Facades\Http::response([
            ['name'=>'Desa Ciburial','display_name'=>'Ciburial, Bandung, Jawa Barat','lat'=>'-6.86','lon'=>'107.63','type'=>'village',
                'boundingbox'=>['-6.87', '-6.85', '107.62', '107.64']],
        ])]);
        $user = User::factory()->create(['role'=>'participant','must_complete_profile'=>true,'must_change_password'=>true]);
        $this->actingAs($user)->getJson(route('profile.address-search', ['q'=>'Desa Ciburial']))
            ->assertOk()->assertJsonPath('data.0.name', 'Desa Ciburial')
            ->assertJsonPath('data.0.boundingbox', ['-6.87', '-6.85', '107.62', '107.64']);
    }

    public function test_profile_page_renders_for_participant_and_admin(): void
    {
        foreach (['participant', 'superadmin'] as $role) {
            $user = User::factory()->create(['role'=>$role]);
            $response = $this->actingAs($user)->get(route('profile.edit'))->assertOk();
            if ($role === 'participant') {
                $response->assertSee('name="nip"', false)->assertSee('name="nik"', false)->assertDontSee('name="nip_nik"', false);
                $this->assertMatchesRegularExpression('/container-p-y"\s+data-profile-mode="view"/', $response->getContent());
                $response->assertSee('Edit Profil')
                    ->assertSee('id="profileFields" disabled', false)->assertSee('id="profilePasswordFields" disabled', false);
            } else {
                $this->assertDoesNotMatchRegularExpression('/container-p-y"\s+data-profile-mode=/', $response->getContent());
                $response->assertDontSee('id="profileFields" disabled', false);
            }
        }
    }

    public function test_participant_profile_update_saves_both_identifiers_and_keeps_them_required(): void
    {
        $user = User::factory()->create(['role'=>'participant']);
        $this->actingAs($user)->put(route('profile.update'), $this->payload(['nik'=>'']))->assertSessionHasErrors('nik');
        $this->get(route('profile.edit'))->assertOk()->assertSee('data-profile-mode="edit"', false)
            ->assertDontSee('id="profileFields" disabled', false);
        $this->from(route('profile.edit'))->put(route('profile.update'), $this->payload())
            ->assertSessionHasNoErrors()->assertSessionHas('profile_saved')->assertRedirect(route('profile.edit'));
        $this->get(route('profile.edit'))->assertOk()->assertSee('data-profile-mode="view"', false)
            ->assertSee('id="profileSavedModal"', false)->assertSee('Berhasil disimpan');
        $this->assertSame('199001012020011001', $user->fresh()->nip);
        $this->assertSame('3201010101900001', $user->fresh()->nik);
        $this->assertSame($user->fresh()->nip, $user->fresh()->nip_nik);
    }

    public function test_profile_photo_is_replaced_on_save(): void
    {
        $disk = \Illuminate\Support\Facades\Storage::fake('public');
        $previous = 'avatars/foto-lama.jpg';
        $disk->put($previous, 'foto lama');
        $user = User::factory()->create(['role' => 'participant', 'profile_photo' => $previous]);

        $this->actingAs($user)->get(route('profile.edit'))->assertOk()
            ->assertSee('id="profilePhotoCropModal"', false);
        $disk->assertExists($previous);
        $this->put(route('profile.update'), $this->payload([
            'profile_photo' => \Illuminate\Http\UploadedFile::fake()->image('foto-profil.jpg', 512, 512),
        ]))->assertSessionHasNoErrors()->assertSessionHas('profile_saved');

        $disk->assertExists($user->fresh()->profile_photo);
        $disk->assertMissing($previous);
    }

    public function test_backfill_copies_only_unambiguous_legacy_identifiers(): void
    {
        $nip = User::factory()->create(['nip_nik'=>'198001012020011001']);
        $nik = User::factory()->create(['nip_nik'=>'3201010101800001']);
        $unknown = User::factory()->create(['nip_nik'=>'legacy-123']);
        $duplicate = User::factory()->create(['nip_nik'=>'198501012020011001']);
        User::factory()->create(['nip_nik'=>'198501012020011001']);
        $migration = require database_path('migrations/2026_11_17_151000_backfill_separate_profile_identifiers.php');
        $migration->up();
        $migration->up();
        $this->assertSame($nip->nip_nik, $nip->fresh()->nip);
        $this->assertNull($nip->fresh()->nik);
        $this->assertSame($nik->nip_nik, $nik->fresh()->nik);
        $this->assertNull($unknown->fresh()->nip);
        $this->assertNull($duplicate->fresh()->nip);
    }

    public function test_general_profile_update_saves_new_fields(): void
    {
        $user=User::factory()->create(['role'=>'superadmin','nip_nik'=>'OLD']);

        $this->actingAs($user)->put(route('profile.update'),$this->payload(['nip_nik'=>'199001012020011001','user_type'=>null,'golongan'=>'XIV','status_kepegawaian'=>'PPPK-PW']))
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
