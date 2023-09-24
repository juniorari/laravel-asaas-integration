@extends('layouts.app')
<?php
use App\Models\Purchase;
use App\Models\Payment;
?>
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{ __('You are logged in!') }}
                </div>
            </div>
        </div>
    </div>

    <?php

    //para este propósito, vamos criar apenas uma compra do usuário
    if (!Purchase::where('user_id', Auth::user()->id)->count()) {

        $faker = Faker\Factory::create('pt_BR');
        Purchase::create([
            'user_id' => Auth::user()->id,
            'product_name' => $faker->sentence(4),
            'quantity' => 2,
            'original_value' => 500,
            'discount' => 0,
            'freight' => 100.0,
            'total_value' => 1100,
        ]);

    }

    $purchases = Purchase::where('user_id', Auth::user()->id)->get();

    /** @var Purchase $purchase */
    $purchase = $purchases[0];

    ?>

    <div class="row justify-content-center mt-4">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">Produto</div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Produto</th>
                            <th>Quantidade</th>
                            <th>Unitário</th>
                            <th>Desconto</th>
                            <th>Frete</th>
                            <th>TOTAL</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($purchases as $idx => $item)
                            <tr>
                                <td><?=$idx+1?></td>
                                <td><?=$item['product_name']?></td>
                                <td><?=$item['quantity']?></td>
                                <td><?=$item['original_value']?></td>
                                <td><?=$item['discount']?></td>
                                <td><?=$item['freight']?></td>
                                <td><?=$item['total_value']?></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    <div class="row">
                        <div class="col-6">
                            <div class="card card-primary">
                                <div class="card-header">BOLETO</div>
                                <div class="card-body">
                                    <!-- Para este propósito, vamos considerar a primeira compra que foi criada -->
                                    <button type="button" id="btn_boleto" onclick="boleto('<?=$purchase->id?>')" class="btn btn-info">Pagar com Boleto</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="card card-primary">
                                <div class="card-header">PIX</div>
                                <div class="card-body">
                                    <!-- Para este propósito, vamos considerar a primeira compra que foi criada -->
                                    <button type="button" id="btn_pix" onclick="pix('<?=$purchase->id?>')" class="btn btn-success">Pagar com PIX</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card card-primary">
                                <div class="card-header">CARTÃO</div>
                                <div class="card-body">

                                    <h5 class="text-success mb-2">Parcelas</h5>

                                    <div class="form-group mb-2">
                                        <label for="installment">Selecione a quantidade de parcelas:</label>
                                        <select class="form-control" id="installment" name="payment_installment">

                                            <option value="">À Vista {{ $purchase->formatValue($purchase->total_value) }}</option>
                                            <?php
                                            $value = $purchase->total_value;
                                            for ($i = 2; $i <= 12; $i++) {
                                                $val = ($value / $i);
                                                $val = $purchase->formatValue($val);
                                                ?>
                                            <option value="{{ $i }}">{{ $i }} x R$ {{ $val }}</option>
                                            <?php
                                            }

                                            ?>
                                        </select>
                                    </div>


                                    <h4 class="text-success mb-2 mt-4">Informe os dados do cartão</h4>

                                    <div class="row p-0">
                                        <div class="form-group col-sm-6 mb-2">
                                            <label for="number">Número do cartão:</label>
                                            <input id="number" type="number" class="form-control">
                                        </div>

                                        <div class="form-group col-sm-6 mb-2">
                                            <label for="holder_name">Nome impresso no cartão:</label>
                                            <input id="holder_name" type="text" class="form-control">
                                        </div>
                                    </div>

                                    <div class="row p-0">
                                        <div class="form-group col-sm-4 mb-2 pl-0">
                                            <label for="expiry_month">Mês Validade</label>
                                            <input id="expiry_month" type="number" class="form-control">
                                        </div>

                                        <div class="form-group col-sm-4 mb-2 pl-0 pr-0">
                                            <label for="expiry_year">Ano Validade</label>
                                            <input id="expiry_year" type="number" class="form-control">
                                        </div>

                                        <div class="form-group col-sm-4 mb-2 pr-0">
                                            <label for="ccv">Código (CVV)</label>
                                            <input id="ccv" type="number" class="form-control">
                                        </div>
                                    </div>

                                    <!-- Para este propósito, vamos considerar a primeira compra que foi criada -->
                                    <button type="button" id="btn_cc" onclick="cc('<?=$purchase->id?>')" class="btn btn-primary mt-3">Pagar com Cartão de Crédito</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center mt-4">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">Meus Pagamentos</div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr class="table-header">
                            <th class="text-center">#</th>
                            <th class="text-center">Status</th>
                            <th>Descrição</th>
                            <th class="text-center">Valor</th>
                            <th class="text-center">Vencimento</th>
                            <th class="text-center">Tipo</th>
                            <th class="text-center">Abrir</th>
                            <th class="text-center">Invoice</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $payments = Payment::where('user_id', Auth::user()->id)->get();
                        ?>
                        @foreach($payments as $idx => $item)
                            <tr>
                                <td class="text-center"><?=$idx+1?></td>
                                <td class="text-center"><?=$item['status']?></td>
                                <td><?=$item['description']?></td>
                                <td class="text-center"><?=number_format($item['value'], 2, ',','.')?></td>
                                <td class="text-center"><?=date('d/m/Y', $item['dus_date'])?></td>
                                <td class="text-center"><?=$item['billing_type']?></td>
                                <td class="text-center">
                                    <a href="{{ route('show', $item['id']) }}" class="btn btn-sm btn-outline-danger">Abrir</a>
                                </td>
                                <td class="text-center">
                                    <?php
                                    if ($item['invoice_url']) { ?>
                                        <a href="<?=$item['invoice_url']?>" class="btn btn-sm btn-outline-primary">Abrir</a>
                                    <?php } ?>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')

<script>

    const TYPE_BOLETO = 'BOLETO';
    const TYPE_PIX    = 'PIX';
    const TYPE_CARTAO = 'CREDIT_CARD';

    function boleto(purc_id) {

        let $btn = $('#btn_boleto');
        send(purc_id, TYPE_BOLETO, $btn, []);

    }

    function pix(purc_id) {

        let $btn = $('#btn_pix');
        send(purc_id, TYPE_PIX, $btn, []);

    }

    function cc(purc_id) {

        let $btn = $('#btn_cc');

        let installment = $('#installment').val();
        let number = $('#number').val();
        let holder_name = $('#holder_name').val();
        let expiry_month = $('#expiry_month').val();
        let expiry_year = $('#expiry_year').val();
        let ccv = $('#ccv').val();

        send(purc_id, TYPE_CARTAO, $btn, {
            installment: installment,
            number: number,
            holder_name: holder_name,
            expiry_month: expiry_month,
            expiry_year: expiry_year,
            ccv: ccv
        });

    }

    function send(purc_id, type, $btn, dataCC) {

        let user_id = '<?=Auth::user()->id?>';
        let txt = $btn.html();
        $btn.attr('disabled', true).html('Aguarde...');

        axios.post('/api/payments', {
            user_id: user_id,
            purchase_id: purc_id,
            type: type,
            data_cc: dataCC
        }).then(response => {
            console.log(response);
            // window.location = 'show/' + response.data.id
        })
        .catch(error => {
            let response = error.response;
            console.log(response);
            // switch (response.status) {
            //     case 422:
            //         alert('VERIFIQUE OS ERROS NO FORMULÁRIO:\n\n' + response.data);
            //         break;
            //
            //     default:
                    alert(response.data);
            // }
        })
        .finally(() => {
            $btn.removeAttr('disabled').html(txt);
        });

    }

    function formatValue(value){
        value = parseFloat(value);
        return value.toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});
    }

</script>
@endsection
