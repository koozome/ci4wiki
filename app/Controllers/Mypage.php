<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use RobThree\Auth\Providers\Qr\QRServerProvider;
use RobThree\Auth\TwoFactorAuth;

class Mypage extends BaseController
{
    public function index(): string
    {
        $user = auth()->user();
        return $this->render('mypage/index', ['user' => $user]);
    }

    /** email 2FA を有効化 */
    public function enableEmail(): RedirectResponse
    {
        $this->updateTwofa(auth()->user()->id, 'email', null);
        return redirect()->to('mypage')->with('message', 'メール認証を有効にしました。');
    }

    /** TOTP セットアップ画面の表示 & QR 確認後の保存 */
    public function totpSetup(): string|RedirectResponse
    {
        $user = auth()->user();
        // QRServerProvider(false) = SSL検証無効（ローカル環境対応）
        $tfa  = new TwoFactorAuth(new QRServerProvider(false), 'Wiki');

        if ($this->request->getMethod() === 'POST') {
            $secret = (string) $this->request->getPost('secret');
            $code   = (string) $this->request->getPost('code');

            if (! $tfa->verifyCode($secret, $code)) {
                return $this->render('mypage/totp_setup', [
                    'user'      => $user,
                    'secret'    => $secret,
                    'qrDataUri' => $tfa->getQRCodeImageAsDataUri($user->email, $secret),
                    'error'     => '認証コードが正しくありません。アプリのコードを再入力してください。',
                ]);
            }

            $this->updateTwofa($user->id, 'totp', $secret);
            return redirect()->to('mypage')->with('message', '認証アプリ（TOTP）を有効にしました。');
        }

        $secret = $tfa->createSecret();
        return $this->render('mypage/totp_setup', [
            'user'      => $user,
            'secret'    => $secret,
            'qrDataUri' => $tfa->getQRCodeImageAsDataUri($user->email, $secret),
        ]);
    }

    /** 2FA を無効化 */
    public function disable(): RedirectResponse
    {
        $this->updateTwofa(auth()->user()->id, null, null);
        return redirect()->to('mypage')->with('message', '2段階認証を無効にしました。');
    }

    private function updateTwofa(int $userId, ?string $type, ?string $secret): void
    {
        \Config\Database::connect()->table('users')->where('id', $userId)->update([
            'twofa_type'  => $type,
            'totp_secret' => $secret,
        ]);
    }
}
