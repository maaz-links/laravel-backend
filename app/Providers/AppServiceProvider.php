<?php

namespace App\Providers;

//use App\Services\LogService;
//use App\Services\LogServiceInt;
//use Illuminate\Pagination\Paginator;
use App\Models\MailConfig;
use App\Services\CommonService;
use Config;
use Illuminate\Support\ServiceProvider;
use Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //$this->app->singleton(LogServiceInt::class, LogService::class);
        $this->app->singleton(CommonService::class, function ($app) {
            return new CommonService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        //Paginator::useBootstrapFive();
        if (Schema::hasTable('mail_configs')) {
            $mailSettings = MailConfig::first();
            //dd($mailSettings);
            if ($mailSettings) {
                // Config::set('mail.mailer', $mailSettings->mail_mailer);
                // Config::set('mail.host', $mailSettings->mail_host);
                // Config::set('mail.port', $mailSettings->mail_port);
                // Config::set('mail.username', $mailSettings->mail_username);
                // Config::set('mail.password', $mailSettings->mail_password);
                // Config::set('mail.encryption', $mailSettings->mail_encryption);
                // Config::set('mail.from.address', $mailSettings->mail_from_address);
                // Config::set('mail.from.name', $mailSettings->mail_from_name);
                Config::set('mail.default', $mailSettings->mail_mailer);
                Config::set('mail.mailers.smtp.host', $mailSettings->mail_host);
                Config::set('mail.mailers.smtp.port', $mailSettings->mail_port);
                Config::set('mail.mailers.smtp.encryption', $mailSettings->mail_encryption);
                Config::set('mail.mailers.smtp.username', $mailSettings->mail_username);
                Config::set('mail.mailers.smtp.password', $mailSettings->mail_password);
                Config::set('mail.from.address', $mailSettings->mail_from_address);
                Config::set('mail.from.name', $mailSettings->mail_from_name);
            }
        }
    }
}
