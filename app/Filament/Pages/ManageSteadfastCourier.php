<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\Permission;
use App\Services\SettingsService;
use App\Services\SteadfastCourier;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Enums\VerticalAlignment;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\HtmlString;
use RuntimeException;

class ManageSteadfastCourier extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Steadfast Courier';

    protected static ?string $title = 'Steadfast Courier API';

    protected static ?int $navigationSort = 2;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected string $view = 'filament.pages.manage-steadfast-courier';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can(Permission::SettingsManage->value);
    }

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    /**
     * Masked start of the saved API key; the key itself is never sent to the browser.
     */
    public ?string $savedApiKeyHint = null;

    /**
     * Masked start of the saved secret key; the key itself is never sent to the browser.
     */
    public ?string $savedSecretKeyHint = null;

    public function getSubheading(): string
    {
        return 'কুরিয়ার সেটিংস এবং ব্যালেন্স যাচাই করুন';
    }

    public function mount(SteadfastCourier $steadfast): void
    {
        $this->rememberSavedKeys($steadfast);

        $this->form->fill([
            'steadfast' => [
                'enabled' => $steadfast->enabled(),
                'auto_send' => $steadfast->sendsConfirmedOrders(),
                'base_url' => $steadfast->baseUrl(),
                'api_key' => null,
                'secret_key' => null,
            ],
        ]);
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('সেভ করুন')
                ->icon('heroicon-o-document-check')
                ->keyBindings(['mod+s'])
                ->action(fn (SettingsService $settings, SteadfastCourier $steadfast) => $this->save($settings, $steadfast)),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make()
                    ->schema([
                        Section::make('Steadfast কুরিয়ার সক্রিয় করুন')
                            ->description('অর্ডার ম্যানেজমেন্ট থেকে Steadfast-এ অর্ডার পাঠানোর সুবিধা চালু হবে')
                            ->secondary()
                            ->compact()
                            ->afterHeader([
                                Text::make('Connected')
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-o-check-circle')
                                    ->visible(fn (SteadfastCourier $steadfast): bool => $steadfast->connected()),

                                Text::make('Not connected')
                                    ->badge()
                                    ->color('gray')
                                    ->icon('heroicon-o-x-circle')
                                    ->visible(fn (SteadfastCourier $steadfast): bool => $steadfast->hasCredentials() && ! $steadfast->connected()),

                                Toggle::make('steadfast.enabled')
                                    ->hiddenLabel()
                                    ->rule(fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                                        if ($value && (! $this->hasKey('api_key') || ! $this->hasKey('secret_key'))) {
                                            $fail('Steadfast চালু করতে API key এবং secret key দিন।');
                                        }
                                    }),
                            ]),

                        Toggle::make('steadfast.auto_send')
                            ->label('অর্ডার Confirm করলে সাথে সাথে Steadfast-এ পাঠান')
                            ->helperText('নতুন অর্ডার সরাসরি যাবে না, Confirm করার পরেই যাবে। বন্ধ রাখলে Confirm করা অর্ডার "Send to Steadfast" বাটন দিয়ে হাতে পাঠাতে হবে')
                            ->default(true),

                        TextInput::make('steadfast.base_url')
                            ->label('API Base URL')
                            ->url()
                            ->required()
                            ->maxLength(255)
                            ->default(SteadfastCourier::DEFAULT_BASE_URL)
                            ->placeholder(SteadfastCourier::DEFAULT_BASE_URL),

                        $this->secretInput('steadfast.api_key', 'API Key', fn (): ?string => $this->savedApiKeyHint)
                            ->helperText('ফাঁকা রাখলে আগের API key অপরিবর্তিত থাকবে'),

                        $this->secretInput('steadfast.secret_key', 'Secret Key', fn (): ?string => $this->savedSecretKeyHint)
                            ->helperText('API key এবং secret key encrypted অবস্থায় server-side save হবে'),

                        Flex::make([
                            Actions::make([
                                Action::make('testConnection')
                                    ->label('সংযোগ পরীক্ষা করুন')
                                    ->icon('heroicon-o-arrow-path')
                                    ->color('gray')
                                    ->tooltip('নতুন দেওয়া key থাকলে আগে সেভ হবে, তারপর ব্যালেন্স যাচাই করা হবে')
                                    ->action(fn (SettingsService $settings, SteadfastCourier $steadfast) => $this->testConnection($settings, $steadfast)),

                                Action::make('removeKeys')
                                    ->label('Key মুছে ফেলুন')
                                    ->icon('heroicon-o-trash')
                                    ->color('danger')
                                    ->outlined()
                                    ->visible(fn (): bool => $this->savedApiKeyHint !== null || $this->savedSecretKeyHint !== null)
                                    ->requiresConfirmation()
                                    ->modalHeading('Steadfast API key মুছে ফেলবেন?')
                                    ->modalDescription('API key এবং secret key মুছে যাবে, Steadfast বন্ধ হয়ে যাবে। আবার চালু করতে নতুন key দিতে হবে।')
                                    ->modalSubmitActionLabel('হ্যাঁ, মুছে ফেলুন')
                                    ->action(fn (SettingsService $settings, SteadfastCourier $steadfast) => $this->removeKeys($settings, $steadfast)),
                            ]),

                            Text::make(fn (SteadfastCourier $steadfast): HtmlString => $this->balanceSummary($steadfast))
                                ->grow(false),
                        ])->verticalAlignment(VerticalAlignment::Center),
                    ]),
            ]);
    }

    public function save(SettingsService $settings, SteadfastCourier $steadfast): void
    {
        $previousBaseUrl = $steadfast->baseUrl();
        $state = $this->form->getState()['steadfast'];

        $settings->setMany('steadfast', $state);
        $settings->flush();

        $this->data['steadfast']['api_key'] = null;
        $this->data['steadfast']['secret_key'] = null;
        $this->rememberSavedKeys($steadfast);

        $credentialsChanged = isset($state['api_key']) || isset($state['secret_key']) || $steadfast->baseUrl() !== $previousBaseUrl;

        if (! $credentialsChanged) {
            Notification::make()->title('Steadfast সেটিংস সেভ হয়েছে')->success()->send();

            return;
        }

        $steadfast->forgetBalance();

        if (! $steadfast->hasCredentials()) {
            Notification::make()
                ->title('Steadfast সেটিংস সেভ হয়েছে')
                ->body('সংযোগ করতে API key এবং secret key দুটোই দিন।')
                ->warning()
                ->send();

            return;
        }

        $this->reportBalance($steadfast);
    }

    /**
     * Saves keys typed into the form first, so the check always uses what the admin entered.
     */
    public function testConnection(SettingsService $settings, SteadfastCourier $steadfast): void
    {
        $hasUnsavedCredentials = filled($this->data['steadfast']['api_key'] ?? null)
            || filled($this->data['steadfast']['secret_key'] ?? null)
            || rtrim(trim((string) ($this->data['steadfast']['base_url'] ?? '')), '/') !== $steadfast->baseUrl();

        if ($hasUnsavedCredentials) {
            $this->save($settings, $steadfast);

            return;
        }

        $this->reportBalance($steadfast);
    }

    /**
     * Deletes both saved keys and switches Steadfast off, since it cannot run without them.
     */
    public function removeKeys(SettingsService $settings, SteadfastCourier $steadfast): void
    {
        $settings->setMany('steadfast', ['api_key' => null, 'secret_key' => null, 'enabled' => false]);
        $settings->flush();
        $steadfast->forgetBalance();

        $this->data['steadfast']['enabled'] = false;
        $this->data['steadfast']['api_key'] = null;
        $this->data['steadfast']['secret_key'] = null;
        $this->rememberSavedKeys($steadfast);

        Notification::make()->title('Steadfast key মুছে ফেলা হয়েছে')->success()->send();
    }

    /**
     * Checks the saved keys against Steadfast and reports the balance or the error.
     */
    private function reportBalance(SteadfastCourier $steadfast): void
    {
        try {
            $balance = $steadfast->checkBalance();
        } catch (RequestException $exception) {
            Notification::make()
                ->title('Steadfast সংযোগ ব্যর্থ হয়েছে')
                ->body($exception->response->json('message') ?? "Steadfast HTTP {$exception->response->status()} ফেরত দিয়েছে। API key এবং secret key যাচাই করুন।")
                ->danger()
                ->persistent()
                ->send();

            return;
        } catch (ConnectionException|RuntimeException $exception) {
            Notification::make()
                ->title('Steadfast সংযোগ ব্যর্থ হয়েছে')
                ->body($exception->getMessage())
                ->danger()
                ->persistent()
                ->send();

            return;
        }

        Notification::make()
            ->title('Steadfast সংযুক্ত হয়েছে')
            ->body('বর্তমান ব্যালেন্স: ৳'.self::banglaDigits(self::formatAmount($balance)))
            ->success()
            ->send();
    }

    private function secretInput(string $statePath, string $label, Closure $savedHint): TextInput
    {
        return TextInput::make($statePath)
            ->label($label)
            ->password()
            ->revealable()
            ->autocomplete('new-password')
            ->maxLength(500)
            ->placeholder(fn (): string => $savedHint() ?? 'Steadfast পোর্টাল থেকে কপি করে বসান')
            ->dehydrated(fn (?string $state): bool => filled($state))
            ->dehydrateStateUsing(fn (string $state): string => SteadfastCourier::encryptKey($state));
    }

    private function hasKey(string $key): bool
    {
        $savedHint = $key === 'api_key' ? $this->savedApiKeyHint : $this->savedSecretKeyHint;

        return $savedHint !== null || filled($this->data['steadfast'][$key] ?? null);
    }

    private function rememberSavedKeys(SteadfastCourier $steadfast): void
    {
        $this->savedApiKeyHint = self::maskKey($steadfast->apiKey());
        $this->savedSecretKeyHint = self::maskKey($steadfast->secretKey());
    }

    private function balanceSummary(SteadfastCourier $steadfast): HtmlString
    {
        $balance = $steadfast->lastBalance();

        if ($balance === null || ! $steadfast->hasCredentials()) {
            return new HtmlString('<span class="text-sm text-gray-500 dark:text-gray-400">ব্যালেন্স দেখতে সংযোগ পরীক্ষা করুন</span>');
        }

        $checkedAt = $steadfast->lastCheckedAt()?->setTimezone(config('app.display_timezone'))->format('j/n/Y, g:i:s A');

        return new HtmlString(sprintf(
            '<span class="font-semibold text-gray-950 dark:text-white">বর্তমান ব্যালেন্স: ৳%s</span>%s',
            e(self::banglaDigits(self::formatAmount($balance))),
            $checkedAt === null ? '' : '<span class="ms-2 text-xs text-gray-500 dark:text-gray-400">শেষ চেক: '.e(self::banglaDigits($checkedAt)).'</span>',
        ));
    }

    private static function maskKey(?string $key): ?string
    {
        return $key === null ? null : mb_substr($key, 0, 4).str_repeat('•', 24);
    }

    private static function formatAmount(float $amount): string
    {
        return rtrim(rtrim(number_format($amount, 2, '.', ''), '0'), '.');
    }

    private static function banglaDigits(string $value): string
    {
        return strtr($value, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯']);
    }
}
