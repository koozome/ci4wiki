<?php

declare(strict_types=1);

namespace App\Authentication\Actions;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Authentication\Actions\ActionInterface;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Entities\UserIdentity;
use CodeIgniter\Shield\Exceptions\RuntimeException;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Traits\Viewable;
use RobThree\Auth\Providers\Qr\QRServerProvider;
use RobThree\Auth\TwoFactorAuth;

/**
 * ユーザーごとに 2FA 方式（off / email / totp）を切り替えるカスタム Action。
 * Shield の Auth.actions['login'] に登録して使用する。
 */
class UserDefined2FA implements ActionInterface
{
    use Viewable;

    /** バイパス用 identity の type 名 */
    private string $type = 'user_2fa';

    // ------------------------------------------------------------------ //
    // ActionInterface 実装
    // ------------------------------------------------------------------ //

    /**
     * startUpAction() から呼ばれる。バイパス用 identity を生成して返す。
     */
    public function createIdentity(User $user): string
    {
        $model = model(UserIdentityModel::class);
        $model->deleteIdentitiesByType($user, $this->type);

        $generator = static fn (): string => random_string('alnum', 32);

        return $model->createCodeIdentity(
            $user,
            ['type' => $this->type, 'name' => 'bypass', 'extra' => ''],
            $generator,
        );
    }

    /**
     * auth-action-show にアクセスされたときに呼ばれる。
     * twofa_type に応じてフォームを返すか即座にログインを完了する。
     */
    public function show(): RedirectResponse|string
    {
        /** @var Session $auth */
        $auth = auth('session')->getAuthenticator();
        $user = $auth->getPendingUser();
        if ($user === null) {
            throw new RuntimeException('Cannot get the pending login User.');
        }

        $type = $user->twofa_type ?? null;

        if ($type === null) {
            // 2FA 無効 → バイパス identity でそのままログイン完了
            $identity = $this->getBypassIdentity($user);
            $auth->checkAction($identity, $identity->secret);
            return redirect()->to(config('Auth')->loginRedirect());
        }

        if ($type === 'email') {
            // email OTP identity を生成してフォームを表示
            $this->createEmailIdentity($user);
            return $this->view('auth/2fa_email_show', ['user' => $user]);
        }

        // totp
        return $this->view('auth/2fa_totp_verify');
    }

    /**
     * email 2FA の「送信」ボタン押下時 → OTP メールを送信してコード入力画面へ。
     */
    public function handle(IncomingRequest $request): RedirectResponse|string
    {
        /** @var Session $auth */
        $auth = auth('session')->getAuthenticator();
        $user = $auth->getPendingUser();
        if ($user === null) {
            throw new RuntimeException('Cannot get the pending login User.');
        }

        if (($user->twofa_type ?? null) !== 'email') {
            return redirect()->route('auth-action-show');
        }

        $submitted = $request->getPost('email');
        if (empty($submitted) || $submitted !== $user->email) {
            return redirect()->route('auth-action-show')
                ->with('error', '入力されたメールアドレスが一致しません。');
        }

        $identity = $this->getEmailIdentity($user);
        if ($identity === null) {
            return redirect()->route('auth-action-show')
                ->with('error', '認証情報が見つかりません。再度ログインしてください。');
        }

        $this->sendEmail($user, $identity->secret);

        return $this->view('auth/2fa_email_verify');
    }

    /**
     * コード検証。email / totp それぞれで処理を分岐。
     */
    public function verify(IncomingRequest $request): RedirectResponse|string
    {
        /** @var Session $auth */
        $auth = auth('session')->getAuthenticator();
        $user = $auth->getPendingUser();
        if ($user === null) {
            throw new RuntimeException('Cannot get the pending login User.');
        }

        $token = (string) $request->getPost('token');
        $type  = $user->twofa_type ?? null;

        if ($type === 'email') {
            $identity = $this->getEmailIdentity($user);
            if ($identity === null || ! $auth->checkAction($identity, $token)) {
                session()->setFlashdata('error', '認証コードが正しくありません。');
                return $this->view('auth/2fa_email_verify');
            }
            // checkAction が completeLogin まで処理してくれる
            return redirect()->to(config('Auth')->loginRedirect());
        }

        if ($type === 'totp') {
            $tfa = new TwoFactorAuth(new QRServerProvider(false), 'Wiki');
            if (! $tfa->verifyCode((string) $user->totp_secret, $token)) {
                session()->setFlashdata('error', '認証コードが正しくありません。');
                return $this->view('auth/2fa_totp_verify');
            }
            // バイパス identity を使ってログイン完了
            $bypass = $this->getBypassIdentity($user);
            $auth->checkAction($bypass, $bypass->secret);
            return redirect()->to(config('Auth')->loginRedirect());
        }

        return redirect()->route('auth-action-show');
    }

    public function getType(): string
    {
        return $this->type;
    }

    // ------------------------------------------------------------------ //
    // 内部ヘルパー
    // ------------------------------------------------------------------ //

    private function getBypassIdentity(User $user): UserIdentity
    {
        $identity = model(UserIdentityModel::class)->getIdentityByType($user, $this->type);
        if ($identity === null) {
            throw new RuntimeException('Bypass identity not found.');
        }
        return $identity;
    }

    private function createEmailIdentity(User $user): void
    {
        $model = model(UserIdentityModel::class);
        $model->deleteIdentitiesByType($user, Session::ID_TYPE_EMAIL_2FA);
        $generator = static fn (): string => str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $model->createCodeIdentity(
            $user,
            ['type' => Session::ID_TYPE_EMAIL_2FA, 'name' => 'login', 'extra' => ''],
            $generator,
        );
    }

    private function getEmailIdentity(User $user): ?UserIdentity
    {
        return model(UserIdentityModel::class)->getIdentityByType($user, Session::ID_TYPE_EMAIL_2FA);
    }

    private function sendEmail(User $user, string $code): void
    {
        helper('email');
        $mail = emailer(['mailType' => 'html'])
            ->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? 'Wiki');
        $mail->setTo($user->email);
        $mail->setSubject('ログイン認証コード');
        $mail->setMessage(
            "<p>以下の認証コードを入力してください。このコードは10分間有効です。</p>"
            . "<p style='font-size:2em;letter-spacing:.2em'><strong>{$code}</strong></p>"
        );
        $mail->send(false);
        $mail->clear();
    }
}
