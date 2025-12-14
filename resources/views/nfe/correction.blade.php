@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">Carta de Correção Eletrônica (CC-e)</div>
                <div class="card-body">
                    <h4>NF-e: {{ $nfe->numero }} - Série: {{ $nfe->serie }}</h4>
                    <p>Chave: {{ $nfe->chave }}</p>
                    <hr>

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('nfe.correction.store', $nfe->id) }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="correction_text">Correção (Mínimo 15 caracteres)</label>
                            <textarea name="correction_text" id="correction_text" rows="5"
                                class="form-control @error('correction_text') is-invalid @enderror">{{ old('correction_text') }}</textarea>
                            @error('correction_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Descreva a correção de forma clara. Cuidado: nem todos os campos podem
                                ser corrigidos via CC-e.</small>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('nfe.index') }}" class="btn btn-secondary">Voltar</a>
                            <button type="submit" class="btn btn-warning">Transmitir Correção</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection