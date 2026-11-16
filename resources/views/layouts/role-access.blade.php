@if(count(Auth::user()->assignedRoles())>1)
<li class="menu-header small text-uppercase"><span class="menu-header-text">Akses Peran</span></li>
@foreach(Auth::user()->assignedRoles() as $accessRole)
<li class="menu-item {{ Auth::user()->role===$accessRole?'active':'' }}"><form action="{{ route('roles.switch') }}" method="POST">@csrf<input type="hidden" name="role" value="{{ $accessRole }}"><button type="submit" class="menu-link border-0 text-start w-100" style="background:transparent"><i class="menu-icon bx {{ Auth::user()->role===$accessRole?'bx-radio-circle-marked':'bx-radio-circle' }}"></i><div>{{ \App\Support\UserRoles::LABELS[$accessRole]??$accessRole }}</div></button></form></li>
@endforeach
<li class="px-4 py-2"><small class="text-muted">Pilih peran untuk membuka menu dan kewenangannya.</small></li>
@endif
