<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @group email-verification
     * 会員登録後、認証メールが送信される
     *
     * @return void
     */
    public function 会員登録後_認証メールが送信される()
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /**
     * @test
     * @group email-verification
     * メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する
     *
     * @return void
     */
    public function メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する()
    {
        Notification::fake();

        $user = User::factory()->create(['email_verified_at' => null]);
        $this->actingAs($user);

        // 保護されたルートにアクセスすると認証通知ページにリダイレクトされる
        $response = $this->get('/attendance');
        $response->assertRedirect(route('verification.notice'));

        // 認証通知ページに「認証はこちらから」ボタンが表示されていることを確認
        $verificationNoticeResponse = $this->get(route('verification.notice'));
        $verificationNoticeResponse->assertSee('認証はこちらから');

        // 「認証はこちらから」ボタンを押下 (再送信リクエスト)
        $resendResponse = $this->post(route('verification.send'));

        // 再送信後、認証通知ページにリダイレクトされ、成功メッセージが表示される
        $resendResponse->assertRedirect(route('verification.notice'));
        $resendResponse->assertSessionHas('status', 'verification-link-sent');

        // 新しい認証メールが送信されたことを確認
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /**
     * @test
     * @group email-verification
     * メール認証サイトのメール認証を完了すると、勤怠登録画面に遷移する
     *
     * @return void
     */
    public function メール認証サイトのメール認証を完了すると_勤怠登録画面に遷移する()
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        $this->actingAs($user);

        // 認証URLを生成
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // 認証URLにアクセス
        $response = $this->get($verificationUrl);

        // ホームページにリダイレクトされることを確認 (その後/attendanceにリダイレクトされる)
        $response->assertRedirect(route('attendance', ['verified' => 1]));

        // ユーザーが認証済みになったことを確認
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}
