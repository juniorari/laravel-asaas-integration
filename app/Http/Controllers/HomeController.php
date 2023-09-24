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
                if($payment->installment_token){
                    $response = $this->asaasService->getPaymentInstallment($payment->installment_token);
                    if($response['status'] == 200){
                        $response['data'] = $response['data']['data'];
                    }
                }
                else{
                    $response = $this->asaasService->getPayment($payment->asaas_id);
                    if($response['status'] == 200){
                        $response['data'] = [$response['data']];
                    }
                }
        }
        $response->type = $payment->billing_type;

        return view('show', [
            'response' => $response
        ]);
    }
}
