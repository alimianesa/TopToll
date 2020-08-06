<?php

namespace alimianesa\TopToll\Http\Controllers;

use alimianesa\TopToll\Resources\PlateConverterHelper;
use Alive2212\LaravelSmartResponse\ResponseModel;
use Alive2212\LaravelSmartResponse\SmartResponse;
use Alive2212\LaravelSmartRestful\SmartCrudController;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TollController extends SmartCrudController
{
    protected $validateAddPlateRequest =  [
        'plate_number'   => 'required',
    ];

    /**
     * @inheritDoc
     */
    public function initController()
    {
        // TODO: Implement initController() method.
    }

    /**
     * @return |null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getTopAccessToken ()
    {
        $accessTokenCache = Cache::get('topAccessToken');

        if (!is_null($accessTokenCache) &&
            !is_null($accessTokenCache['access_token'])
            && $accessTokenCache['expires_at'] > Carbon::now()
        ) {
            Log::info('TollController.getTopAccessToken => ' . $accessTokenCache['access_token']);
            return $accessTokenCache['access_token'];
        }

        Log::info('TollController.getTopAccessToken => not cached');

        $clients = new Client();
        $uri =  config('toptoll.top-server') . config('toptoll.top-prefix'). '‫‪/Account/Login‬‬' ;
        try {
            $loginResponse = $clients->request('POST', $uri
                , [
                    'timeout' => 30,
                    'headers' => [
                        'Content-Type' => 'application/json-patch+json',
                        'Accept' => 'application/json',
                    ],
                    'json' => [
                        'UserName' => config('toptoll.username'),
                        'Password' => config('toptoll.password'),
                    ]
                ]);

            $data = json_decode($loginResponse->getBody()->getContents(), true)['Data'];

            if (is_null($data)) {
                return null;
            }

            Cache::put('topAccessToken', [
                "access_token" => $data['Token'] ,
                "expires_at" => $data['ExpireDate']
            ], 24* 60 * 60);

            return $data['Token'];
        } catch (RequestException $e) {
            return null;
        }
    }

    public function addPlate(Request $request)
    {
        $response = new ResponseModel();

        $validationErrors = $this->checkRequestValidation($request, $this->validateAddPlateRequest);
        if (!is_null($validationErrors)) {
            $response->setStatusCode(422);
            $response->setMessage('اطلاعاتی را که وارد کردید را بررسی کنید.');
            $response->setError(($validationErrors->toArray()));
            return SmartResponse::response($response);
        }

        $topAccessToken = $this->getTopAccessToken();
        if (is_null($topAccessToken)) {
            $response->setStatusCode(401);
            $response->setMessage('نام کاربری یا رمز عبور صحیح نمی باشد');
            return SmartResponse::response($response);
        }

        $plateConverter = new PlateConverterHelper();
        $plate_number = $plateConverter->plateToInt($request->plate_number);


        $clients = new Client();
        $uri =  config('toptoll.top-server') . config('toptoll.top-prefix'). '/Toll/AddPlate' ;
        try {
            $loginResponse = $clients->request('POST', $uri
                , [
                    'timeout' => 30,
                    'headers' => [
                        'Authorization' => 'Bearer ' . $topAccessToken,
                        'Content-Type' => 'application/json-patch+json',
                        'Accept' => 'application/json',
                    ],
                    'json' => [
                        'Pan'      => $request->pan,
                        '‫‪Part1‬‬'    => (int) substr($plate_number,0 ,2),
                        '‫‪LetterId‬‬' => (int) substr($plate_number,2 ,2),
                        '‫‪Part2'    => (int) substr($plate_number,4 ,3),
                        'Code'     => (int) substr($plate_number,7 ,2),
                        '‫‪ClassId‬‬'  => $request->class_id,
                    ]
                ]);

            $data = json_decode($loginResponse->getBody()->getContents(), true);

            if (is_null($data)) {
                $response->setStatusCode($loginResponse->getStatusCode() > 100 ? $loginResponse->getStatusCode() : 400);
                $response->setMessage('خطا در دریافت اطلاعات');
                return SmartResponse::response($response);
            }

            $response->setStatusCode(200);
            $response->setData(collect([
                'data' => $data['Data']
            ]));
            $response->setMessage($data["Message"]);
            return SmartResponse::response($response);
        } catch (RequestException $e) {
            $response->setStatusCode($e->getCode() > 100 ? $e->getCode() : 400);
            $response->setMessage('خطا در دریافت اطلاعات');
            return SmartResponse::response($response);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getBill(Request $request)
    {
        $response = new ResponseModel();

        $validationErrors = $this->checkRequestValidation($request, $this->validateAddPlateRequest);
        if (!is_null($validationErrors)) {
            $response->setStatusCode(422);
            $response->setMessage('اطلاعاتی را که وارد کردید را بررسی کنید.');
            $response->setError(($validationErrors->toArray()));
            return SmartResponse::response($response);
        }

        $plateConverter = new PlateConverterHelper();
        $plate_number = $plateConverter->plateToInt($request->plate_number);

        $topAccessToken = $this->getTopAccessToken();
        if (is_null($topAccessToken)) {
            $response->setStatusCode(401);
            $response->setMessage('نام کاربری یا رمز عبور صحیح نمی باشد');
            return SmartResponse::response($response);
        }

        $clients = new Client();
        $uri =  config('toptoll.top-server') . config('toptoll.top-prefix'). '/Toll/GetBill' ;
        try {
            $loginResponse = $clients->request('POST', $uri
                , [
                    'timeout' => 30,
                    'headers' => [
                        'Authorization' => 'Bearer ' . $topAccessToken,
                        'Content-Type' => 'application/json-patch+json',
                        'Accept' => 'application/json',
                    ],
                    'json' => [
                        '‫‪PlateNumber‬‬' => $plate_number
                    ]
                ]);

            $data = json_decode($loginResponse->getBody()->getContents(), true);

            if (is_null($data)) {
                $response->setStatusCode($loginResponse->getStatusCode() > 100 ? $loginResponse->getStatusCode() : 400);
                $response->setMessage('خطا در دریافت اطلاعات');
                return SmartResponse::response($response);
            }

            $response->setStatusCode(200);
            $response->setData(collect([
                'data' => $data['Data']
            ]));
            $response->setMessage($data["Message"]);
            return SmartResponse::response($response);

        } catch (RequestException $e) {
            $response->setStatusCode(400);
            $response->setMessage('خطا در دریافت اطلاعات');
            return SmartResponse::response($response);
        }
    }

}
