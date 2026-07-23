<h1>Selamat datang, {{ auth()->user()->email }}</h1>
<form method="POST" action="{{ route('admin.logout') }}">
    @csrf
    <button type="submit">Logout</button>
</form>