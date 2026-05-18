<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Reserva;

class ReservaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'profesional_id' => 'required|exists:profesionales,id',
            'servicio_id' => 'required|exists:servicios,id',
            'fecha' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
        ]);

        $existeChoque = Reserva::where('profesional_id', $request->profesional_id)
            ->where('fecha', $request->fecha)
            ->where('hora_inicio', '<', $request->hora_fin)
            ->where('hora_fin', '>', $request->hora_inicio)
            ->exists();


        if($existeChoque){
            return redirect()->back()->withErrors(['fecha' => 'El profesional ya tiene una reserva en ese horario.']);
        }

        $reserva = Reserva::create($validated);

        return redirect()->route('reservas.index')->with('success', 'Reserva creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reserva $reserva)
    {
        return view('reservas.edit', compact('reserva'));
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reserva $reserva)
    {
        $validated = $request->validate([
            'fecha' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'servicio_id' => 'required|exists:servicios,id',
        ]);

        $existeChoque = Reserva::where('profesional_id', $reserva->profesional_id)
            ->where('fecha', $request->fecha)
            ->where('id', '!=', $reserva->id) // Excluir la reserva actual en caso de actualización
            ->where('hora_inicio', '<', $request->hora_fin)
            ->where('hora_fin', '>', $request->hora_inicio)
            ->exists();


        if($existeChoque){
            return redirect()->back()->withErrors([
                'fecha' => 'El profesional ya tiene una reserva en ese horario.']);
        }

        $usoSesion = $reserva->uso_sesion_paquete;

        if($usoSesion) {
            $paquete = $usoSesion->compra_paquete;

            if($request->servicio_id != $paquete->paquete_servicio_id) {
                return redirect()->back()->withErrors([
                    'servicio_id' => 'El nuevo servicio no coincide con el paquete contratado para esta reserva.']);
            }
        }

        $reserva->update($validated);

        return redirect()->route('reservas.index')->with('success', 'Reserva reprogramada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Reserva $reserva)
    {
        $request->validate([
            'motivo_cancelacion' => 'required|string|max:255',
        ]);

        $reserva->update([
            'estado_reserva' => 'cancelada',
            'motivo_cancelacion' => $request->motivo_cancelacion,
        ]);

        $usoSesion = $reserva->uso_sesion_paquete;

        if($usoSesion && $request->uso_sesion_paquete) {
            $paquete = $usoSesion->compra_paquete;

            $paquete->sesiones_disponibles += 1;
            $paquete->sesiones_consumidas -= 1;

            if($paquete->estado_paquete === 'consumido') {
                $paquete->estado_paquete = 'activo';
            }

            $paquete->save();
            $usoSesion->delete();
        }

        return redirect()->route('reservas.index')->with('success', 'Reserva cancelada exitosamente.');
    }
}
