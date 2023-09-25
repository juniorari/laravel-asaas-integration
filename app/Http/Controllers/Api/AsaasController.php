<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AsaasRequest;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\User;
use App\Services\AsaasService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AsaasController extends Controller
{
    const SUCCESS_RESPONSE = 200;

    /** @var AsaasService  */
    private $asaasService;
    /** @var Payment  */
    private $payment;

    public function __construct(
        AsaasService $asaasService,
        Payment $payment
    )
    {
        $this->asaasService = $asaasService;
        $this->payment = $payment;
    }

    public function store(AsaasRequest $request)
    {
        try {

            DB::beginTransaction();

            $user_id = $request->get('user_id');
            $purc_id = $request->get('purchase_id');
            $user = User::where('id', $user_id)->first();
            $purchase = Purchase::where('id', $purc_id)->first();

            //verifica se já tem o esse cliente cadastrado
            $cliente = $this->asaasService->getCliente($user->cpf);

            if ($cliente && count($cliente->data)) {
                $cliente = $cliente->data[0];
            } else {
                //criamos o cliente no Asaas
                $response = $this->asaasService->createCliente($user);

                if ($response['code'] != self::SUCCESS_RESPONSE) {
                    return response()->json($response['data'], $response['code']);
                }
                $cliente = $response['data'];
            }

            if (!$user->id_asaas) {
                $user->update([
                    'id_asaas' => $cliente->id,
                ]);
            }


            //cria o pagamento
            $payment = $this->payment->create([
                'user_id'      => $user->id,
                'customer_id'  => $cliente->id,
                'billing_type' => $request->get('type'),
                'due_date'     => date('Y-m-d', strtotime('+ 5 days')),
                'value'        => floatval($purchase->total_value),
                'description'  => $purchase->product_name
            ]);

            //envia os dados para o Asaas
            switch ($request->get('type')) {
                case 'BOLETO':
                    $response = $this->asaasService->paymentBoleto($payment->toArray());
                    $response['data']->idPayment = $payment->id;
                    // Atualiza as informações do pagamento
                    $payment->update([
                        'asaas_id'      => $response['data']->id,
                        'due_date'      => $response['data']->dueDate,
                        'value'         => $response['data']->value,
                        'invoice_url'   => $response['data']->invoiceUrl,
                        'bank_slip_url' => $response['data']->bankSlipUrl,
                        'status'        => $response['data']->status,
                    ]);
                    break;
                case 'PIX':
                    $response = $this->asaasService->paymentPix($payment->toArray());
                    $response['data']->idPayment = $payment->id;

                    // Atualiza as informações do pagamento
                    $payment->update([
                        'asaas_id'      => $response['data']->id,
                        'due_date'      => $response['data']->dueDate,
                        'value'         => $response['data']->value,
                        'invoice_url'   => $response['data']->invoiceUrl,
                        'bank_slip_url' => $response['data']->bankSlipUrl,
                        'status'        => $response['data']->status,
                    ]);
                    break;

                case 'CREDIT_CARD':

                    $response = $this->asaasService->paymentCC($request, $payment, $user->toArray());

                    if ($response['code'] === self::SUCCESS_RESPONSE) {
                        $dataUpdate = [
                            'asaas_id'      => $response['data']->id,
                            'status'        => $response['data']->status,
                            'bank_slip_url' => $response['data']->transactionReceiptUrl,
                        ];
                        $request = $request->all();

                        if ($request['data_cc']['installment']) {
                            $dataUpdate['installment'] = $request['data_cc']['installment'];
                            $dataUpdate['installment_token'] = $response['data']->installment;
                        }
                        $response['data']->idPayment = $payment->id;

                        $payment->update($dataUpdate);
                    }
                    break;
                default:
                    return response()->json('Tipo inválido!', 400);
            }

            if($response['code'] != 200){
                return response()->json($response['data'], $response['code']);
            }

            // Persiste as informações na base de dados
            DB::commit();

            return response()->json($response['data'], $response['code']);

        } catch (\Exception $e) {
            Log::error('Erro na chamada store', [$e->getMessage(), $e->getTraceAsString()]);
            return response()->json('Erro na requisição: ' . $e->getMessage(), 400);
        }
    }
}
