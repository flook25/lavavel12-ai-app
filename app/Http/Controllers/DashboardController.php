<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Prism;
use Prism\Prism\Enums\Provider;
use Throwable;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard');
    }

    // public function chat()
    // {
    //     try {
    //         $response = Prism::text()
    //             ->using(Provider::Ollama, 'llama3.2:3b')
    //             ->withPrompt('Hello welcome to Thailand')
    //             ->asText();
    //         return $response->text;

    //     } catch (PrismException $e) {
    //         Log::error('Text generation failed:', ['error' => $e->getMessage()]);
    //     } catch (Throwable $e) {
    //         Log::error('Generic error:', ['error' => $e->getMessage()]);
    //     }
    // }

    //  public function chat()
    // {
    //     try {
    //         $response = Prism::text()
    //             ->using(Provider::OpenAI, 'gpt-5-nano')
    //             ->withSystemPrompt('you are asistant for Laravel Framework')
    //             ->withPrompt('Who is the founder of Laravel Framework')
    //             ->asText();
    //         return $response->text;

    //     } catch (PrismException $e) {
    //         Log::error('Text generation failed:', ['error' => $e->getMessage()]);
    //     } catch (Throwable $e) {
    //         Log::error('Generic error:', ['error' => $e->getMessage()]);
    //     }
    // }

    // public function chat(Request $request)
    // {
    //     try {
    //         $textInput = (string) $request->input('text');

    //         return response()->eventStream(function () use ($textInput){
    //            $stream = Prism::text()
    //             ->using(Provider::OpenAI, 'gpt-5-nano')
    //             ->usingTemperature(1) // ถ้า gpt-5 จะปรับ temperature ไม่ได้ (ไม่มีผล) default คือ 1 ถ้า gpt-4 ปรับได้ตามปกติ (0.0-2.0)
    //             ->withSystemPrompt('you are asistant for Laravel Framework')
    //             ->withPrompt($textInput)
    //             ->withClientRetry(3, 100)
    //             ->withClientOptions(['timeout' => 60])
    //             ->withProviderOptions([
    //                 'reasoning' => ['effort' => 'low'], // defualt เป็น medium
    //             ])
    //             ->asStream();

    //             foreach ($stream as $response) {
    //                 yield $response->text;
    //             }
    //         });

            

    //     } catch (PrismException $e) {
    //         Log::error('Text generation failed:', ['error' => $e->getMessage()]);
    //     } catch (Throwable $e) {
    //         Log::error('Generic error:', ['error' => $e->getMessage()]);
    //     }
    // }


    public function chat(Request $request)
    {
        try {
            $messagesPayload = (array) $request->input('messages', []);
            $lastUser = collect($messagesPayload)->reverse()->firstWhere('role','user') ?? [];

            $userText = $lastUser['content'] 
                        ?? collect(Arr::get($lastUser, 'parts', []))->where('type','text')->pluck('text')->implode('\n');


            /** @disregard [OPTIONAL CODE] [OPTIONAL DESCRIPTION] */
            return response()->stream(function () use ($userText){
               $stream = Prism::text()
                ->using(Provider::OpenAI, 'gpt-4.1-nano')
                ->usingTemperature(0.2) // ถ้า gpt-4 จะปรับ temperature ได้ตามปกติ
                ->withSystemPrompt('you are asistant for Laravel Framework')
                ->withPrompt($userText)
                ->withClientRetry(3, 100)
                ->withClientOptions(['timeout' => 60])
                ->withProviderOptions([
                    // 'reasoning' => ['effort' => 'low'], // defualt เป็น medium
                ])
                ->asStream();

                foreach ($stream as $response) {
                    yield $response->text;
                }
            });

            

        } catch (PrismException $e) {
            Log::error('Text generation failed:', ['error' => $e->getMessage()]);
        } catch (Throwable $e) {
            Log::error('Generic error:', ['error' => $e->getMessage()]);
        }
    }

}
