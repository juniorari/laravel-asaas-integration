<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\AsaasService;
use Illuminate\Http\Request;

class HomeController extends Controller
{

    /** @var AsaasService  */
    private $asaasService;

    public function __construct(
        AsaasService $asaasService
    )
    {
        $this->middleware('auth');
        $this->asaasService = $asaasService;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function show(Request $request, $idPayment)
    {
        /** @var Payment $payment */
        $payment = Payment::where('id', $idPayment)->first();
        if (!$payment) {
            return redirect()->route('home')->with([
                'error' => 'Registro não encontrado',
            ]);
        }
        $response = new \stdClass();
        switch ($payment->billing_type){
            case 'PIX':
                $response = $this->asaasService->getPixQrCode($payment->asaas_id)['data'];
                $response->title = 'PIX - FINALIZE O PAGAMENTO';
                break;
            case 'BOLETO':
                $response->boleto = $payment->bank_slip_url;
                $response->title  = 'BOLETO - FINALIZE O PAGAMENTO';
                break;
            case 'CREDIT_CARD':
                $ret = $this->asaasService->getPaymentCC($payment);
                if ($ret['code'] != 200) {
                    return redirect()->route('home')->with([
                        'error' => $ret['data'],
                    ]);
                }

                if($payment->installment){
                    $response->data = $ret['data']->data;
                } else {
                    $response->data = [$ret['data']];
                }
                $response->title  = 'PARCELAS DO CARTÃO DE CRÉDITO';
        }
        $response->type = $payment->billing_type;

        return view('show', [
            'response' => $response
        ]);
    }
}
