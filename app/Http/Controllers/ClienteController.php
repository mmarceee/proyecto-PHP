// <?php

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;

// use App\Models\User;
// use App\Models\Cliente;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Hash;

// class ClienteController extends Controller
// {
//     /**
//      * Display a listing of the resource.
//      */
//     public function index()
//     {
//         //
//     }

//     /**
//      * Show the form for creating a new resource.
//      */
//     public function create()
//     {
//         //
//     }

//     /**
//      * Store a newly created resource in storage.
//      */
//     public function store(Request $request)
// {
//     // 1. Validamos incluyendo la confirmación de contraseña
//     $validated = $request->validate([
//         'name'     => 'required|string|max:255',
//         'apellido' => 'required|string|max:255',
//         'email'    => 'required|string|email|max:255|unique:users,email',
//         'password' => 'required|string|min:8|confirmed', // Obliga a enviar 'password_confirmation'
//         'telefono' => 'required|string|max:255',
//     ]);

//     // 2. Variable para guardar el usuario creado fuera del closure de la transacción
//     $user = null;

//     // 3. Ejecutamos la creación atómica de ambas tablas
//     DB::transaction(function () use ($validated, &$user) {
//         
//         // Creamos el Usuario
//         $user = User::create([
//             'name'           => $validated['name'],
//             'apellido'       => $validated['apellido'],
//             'email'          => $validated['email'],
//             'password'       => Hash::make($validated['password']),
//             'telefono'       => $validated['telefono'],
//             'estado_usuario' => 'activo',
//         ]);

//         // Creamos el Cliente vinculado
//         Cliente::create([
//             'user_id' => $user->id,
//         ]);
//     });

//     // 4. Iniciamos la sesión del nuevo cliente automáticamente
//     Auth::login($user);

//     // 5. Lo mandamos a su panel o a la sección de reservas
//     return redirect()->route('reservas.index')->with('success', '¡Bienvenido! Tu cuenta ha sido creada.');
//     }

//     /**
//      * Display the specified resource.
//      */
//     public function show(string $id)
//     {
//         //
//     }

//     /**
//      * Show the form for editing the specified resource.
//      */
//     public function edit(string $id)
//     {
//         //
//     }

//     /**
//      * Update the specified resource in storage.
//      */
//     public function update(Request $request, string $id)
//     {
//         //
//     }

//     /**
//      * Remove the specified resource from storage.
//      */
//     public function destroy(string $id)
//     {
//         //
//     }
// }
