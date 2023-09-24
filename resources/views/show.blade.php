@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="alert alert-success" role="alert">
                    Operação executada com sucesso
                </div>
                <div class="card">
                    <div class="card-header">{{ $response->title }}</div>

                    <div class="card-body">

                        <div class="row">
                            <div class="col-12 text-center">

                                @if($response->type == 'PIX')
                                <p class="mb-0">Faça a leitura do QR Code abaixo para efetuar o pagamento.</p>
                                <img src="data:image/jpeg;base64,{{ $response->encodedImage }}">
                                <p>Ou copie e cole o código abaixo:</p>
                                <p id="copy" class="alert alert-secondary">{{ $response->payload }}</p>
                                @endif


                                @if($response->type == 'BOLETO')
                                <iframe src="{{ $response->boleto }}" width="100%" height="450px"></iframe>
                                @endif


                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('home') }}" class="btn btn-secondary">VOLTAR</a>
                        @if($response->type == 'PIX')
                        <button id="btnCopy" onclick="copyText()" type="button" class="btn btn-success">Copiar código</button>
                        @endif

                        @if($response->type == 'BOLETO')
                        <a href="{{ $response->boleto }}" title="{{ $response->boleto }}" target="_blank" class="btn btn-success">Acessar link do boleto</a>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        function copyText() {
            let texto = document.getElementById("copy").innerText;
            let btn   = document.getElementById("btnCopy");
            let item  = new ClipboardItem({
                "text/plain": new Blob([texto], { type: "text/plain" })
            });
            navigator.clipboard.write([item]);
            btn.innerText = "Copiado com sucesso";
            setTimeout(function() {btn.innerText = "Copiar código";}, 1500);
        }
    </script>
@endsection
