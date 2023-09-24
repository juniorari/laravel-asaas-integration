<?php


namespace App\Services;


use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Psr\Http\Message\ResponseInterface;

class AsaasService
{

    /** @var Client */
    protected $client;


    protected $API_URL;
    protected $TOKEN;


    public function __construct()
    {

        if (!env('ASAAS_URL') || !env('ASAAS_TOKEN') ) {
            throw new \Exception('Token e/ou URL está faltando no arquivo .env!');
        }

        $this->client   = new Client();
        $this->API_URL  = env('ASAAS_URL');
        $this->TOKEN    = env('ASAAS_TOKEN');
    }

    public function getCliente($cpf)
    {
        $response = $this->sendHttp('GET', 'customers', null, ['cpfCnpj' => $cpf]);
        if ($response['code'] == 200) {
            return $response['data'];
        }
        return false;
    }

    public function createCliente(User $user)
    {
        return $this->sendHttp('POST', 'customers', [
            'name' => $user->name,
            'email' => $user->email,
            'cpfCnpj' => $user->cpf,
            'externalReference' => $user->id,
        ]);
    }

    /**
     * Pagamento por BOLETO
     */
    public function paymentBoleto($data)
    {
        return $this->sendHttp(
            'POST',
            'payments',
            [
                "externalReference" => $data['id'],
                "customer"          => $data['customer_id'],
                "billingType"       => $data['billing_type'],
                "dueDate"           => $data['due_date'],
                "value"             => $data['value'],
                "description"       => $data['description'],
                "discount" => [ //desconto pagamento antes do vencimento
                    "value" => 10,
                    "dueDateLimitDays" => 0
                ],
                "fine" => [ //juros após o vencimento
                    "value" => 1
                ],
                "interest" => [ //multa após o vencimento
                    "value" => 2
                ],
            ]
        );
    }

    /**
     * Pagamento por PIX
     */
    public function paymentPix($data)
    {
        return $this->sendHttp(
            'POST',
            'payments',
            [
                "externalReference" => $data['id'],
                "customer"          => $data['customer_id'],
                "billingType"       => $data['billing_type'],
                "dueDate"           => $data['due_date'],
                "value"             => $data['value'],
                "description"       => $data['description'],
            ]
        );
    }

    public function paymentCC(Request $request, $payment, $user)
    {

        $request = $request->all();

        // Recupera os dados do cartão
        $dataCreditCard = [
            'creditCard' => [
                'holderName'  => $request['data_cc']['holder_name'],
                'number'      => $request['data_cc']['number'],
                'expiryMonth' => $request['data_cc']['expiry_month'],
                'expiryYear'  => $request['data_cc']['expiry_year'],
                'ccv'         => $request['data_cc']['ccv'],
            ],
        ];

        $validate = Validator::make($dataCreditCard, [
            'creditCard.number'      => 'required|string|min:13|max:16',
            'creditCard.holderName'  => 'required|string|max:50',
            'creditCard.expiryMonth' => 'required|string|min:1|max:2',
            'creditCard.expiryYear'  => 'required|string|min:4|max:4',
            'creditCard.ccv'         => 'required|string|min:3|max:3',
        ]);

        if($validate->fails()){
            return [
                'code' => 400,
                'data' => $this->formatErrosValidate($validate)
            ];
        }

        $dataHolderInfo = [
            'creditCardHolderInfo' => [
                'name'          => $user['name'],
                'email'         => $user['email'],
                'cpfCnpj'       => $user['cpf'],
                'postalCode'    => $user['postal_code'],
                'address'       => $user['address'],
                'addressNumber' => $user['address_number'],
                'phone'         => $user['phone'],
            ],
        ];

        $validate = Validator::make($dataHolderInfo['creditCardHolderInfo'], [
            'cpfCnpj'       => 'required|string|cpf|min:11|max:14',
            'name'          => 'required|string|max:50',
            'email'         => 'required|string|email|max:50',
            'postalCode'    => 'required|string|min:8|max:8',
            'address'       => 'required|string|max:255',
            'addressNumber' => 'required|string|max:10',
            'phone'         => 'required|string|min:11|max:20',
        ]);


        if ($validate->fails()) {
            return [
                'code' => 400,
                'data' => $this->formatErrosValidate($validate)
            ];
        }

        $data = [
            'externalReference' => $payment->id,
            'customer'          => $payment->customer_id,
            'billingType'       => $payment->billing_type,
            'dueDate'           => $payment->due_date,
            'description'       => $payment->description,
        ];
        // Validação do valor e parcelas
        $dataValue = ['value' => $payment->value];

        if($request['data_cc']['installment']){
            $dataValue = [
                'totalValue' => $payment->value,
                'installmentCount' => $request['data_cc']['installment'],
            ];
        }

        // Cadastra o pagamento na ASAAS
        $data = array_merge($data, $dataValue, $dataCreditCard, $dataHolderInfo);
        return $this->sendHttp('POST','payments', $data);
    }


    /**
     * Recupera o QRCode do PIX
     * @param $id
     * @return bool|mixed
     */
    public function getPixQrCode($id)
    {
        return $this->sendHttp(
            'GET',
            'payments/'.$id.'/pixQrCode', null, []
        );
    }

    protected function sendHttp($method, $endpoint, $body = null, $params = null)
    {
        $content = [
            'headers' => [
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'access_token' => $this->TOKEN,
            ],
        ];
        if ($params) {
            $content = array_merge(['params' => $params], $content);
        }
        if ($body) {
            $content = array_merge(['body' => json_encode($body)], $content);
        }
        $response = $this->client;
        try {
//            dd($method, $this->API_URL . $url, $content);
            $response = $response->request($method, $this->API_URL . $endpoint, $content);
//            dd($response->getBody()->getContents());
//            return json_decode($response->getBody()->getContents());
            return $this->prepareResponse($response);
        } catch (ClientException $e) {
            return $this->prepareResponse($e->getResponse());
//            dd($e->getMessage(), $e->getTraceAsString(), $e->getResponse()->getStatusCode(), $e->getResponse()->getBody()->getContents());
        } catch (\Exception $e) {
            Log::error('Erro ao chamar API Asaas', [$response->getBody()->getContents(), $e->getMessage(), $e->getTraceAsString()]);
            return $this->prepareResponse(false);
        }
    }

    /**
     * @param ResponseInterface|false $response
     */
    protected function prepareResponse($response)
    {
        if (!$response) {
            return [
                'code' => 400,
                'data' => 'Erro ao chamar API Asaas. Verificar Log!'
            ];
        }
        $code = $response->getStatusCode();

        $data = json_decode($response->getBody()->getContents());

        if(isset($data->errors)){
            $errors = array_map(function ($error) {
                return '- ' . $error->description;
            }, $data->errors);

            return [
                'code' => $code,
                'data'   => implode(PHP_EOL, $errors)
            ];
        }


        $error = '';
        switch ($code) {
            case 400:
                $error = 'Falha na requisição. ';
                break;
            case 401:
                $error = 'API não autorizada. ';
                break;
            case 403:
                $error = 'Requisição não autorizada. ';
                break;
            case 404:
                $error = 'Objeto solicitado não existe. ';
                break;
            case 429:
                $error = 'Muitas requisições. Aguarde um tempo para solicitar novamente. ';
                break;
            case 500:
                $error = 'Algo deu errado. Tente novamente em instantes. ';
                break;
        }


        if (!empty($error)) {
            return [
                'code' => $code,
                'data' => $error
            ];
        }
        // Response success
        return [
            'code' => 200,
            'data'   => $data,
        ];

    }

    protected function formatErrosValidate($validate) {
        $errors = [];
        foreach ($validate->errors()->toArray() as $error) {
            foreach ($error as $value) {
                $errors[] = '- ' . $value;
            }
        }
        return implode(PHP_EOL, $errors);
    }
}
