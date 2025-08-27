<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings;
use App\Models\City;
use App\Models\CompanyProfile;
use App\Models\Country;
use App\Models\Setting;
use App\Models\State;
use Filament\Actions\Action; // Make sure this is imported
use Filament\Forms\Components\Component;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

use function Filament\authorize;

class GeneralSetting extends Page
{
    protected static string $view = 'filament.clusters.settings.pages.general-setting';
    protected static ?string $cluster = Settings::class;
    protected static ?string $title = 'General Settings';
    protected static ?string $navigationLabel = 'General Settings';

    public ?array $data = [];

    #[Locked]
    public ?CompanyProfile $record = null;

    public function getTitle(): string | Htmlable
    {
        return static::$title;
    }

    public static function getNavigationLabel(): string
    {
        return static::$title;
    }

    public function mount(): void
    {
        $companyId = auth()->user()->current_company_id ?? config('app.company_id', 1);
        $this->record = CompanyProfile::firstOrNew([
            'company_id' => $companyId,
        ]);
        abort_unless(static::canView($this->record), 404);

        $this->fillForm();
    }

    public function fillForm(): void
    {
        $companyId = auth()->user()->current_company_id ?? config('app.company_id', 1);
        $data = $this->record->attributesToArray();

        // Add configuration settings
        $data['tax_rate'] = Setting::where('company_id', $companyId)->where('key', 'tax_rate')->value('value') * 100 ?: 10; // Convert to percentage
        $data['default_delivery_fee'] = Setting::where('company_id', $companyId)->where('key', 'default_delivery_fee')->value('value') ?: 0;

        $this->form->fill($data);
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();
            $companyId = auth()->user()->current_company_id ?? config('app.company_id', 1);

            // Update CompanyProfile
            $this->handleRecordUpdate($this->record, [
                'name' => $data['name'],
                'phone_number' => $data['phone_number'],
                'email' => $data['email'],
                'logo' => $data['logo'],
                'country' => $data['country'],
                'state' => $data['state'],
                'street_address' => $data['street_address'],
                'city' => $data['city'],
                'postal_code' => $data['postal_code'],
            ]);

            // Update configuration settings
            Setting::updateOrCreate(
                ['company_id' => $companyId, 'key' => 'tax_rate'],
                ['value' => $data['tax_rate'] / 100] // Convert percentage to decimal
            );
            Setting::updateOrCreate(
                ['company_id' => $companyId, 'key' => 'default_delivery_fee'],
                ['value' => $data['default_delivery_fee']]
            );

            Notification::make()
                ->title('Settings saved successfully')
                ->success()
                ->send();
        } catch (Halt $exception) {
            return;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error saving settings')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getSavedNotification(): Notification
    {
        return Notification::make()
            ->success()
            ->title('Settings saved');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getIdentificationSection(),
                $this->getLocationDetailsSection(),
                $this->getConfigurationSection(),
            ])
            ->model($this->record)
            ->statePath('data')
            ->operation('edit');
    }

    protected function getIdentificationSection(): Component
    {
        return Section::make('Identification')
            ->schema([
                Group::make()
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        PhoneInput::make('phone_number'),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255)
                            ->required(),
                    ])->columns(1),
                FileUpload::make('logo')
                    ->openable()
                    ->maxSize(2048)
                    ->label('Logo')
                    ->visibility('public')
                    ->disk('public')
                    ->directory('logos/company')
                    ->imageResizeMode('contain')
                    ->imageCropAspectRatio('1:1')
                    ->panelAspectRatio('1:1')
                    ->panelLayout('integrated')
                    ->removeUploadedFileButtonPosition('center bottom')
                    ->uploadButtonPosition('center bottom')
                    ->uploadProgressIndicatorPosition('center bottom')
                    ->getUploadedFileNameForStorageUsing(
                        static fn (TemporaryUploadedFile $file): string => (string) str($file->getClientOriginalName())
                            ->prepend(auth()->user()->current_company_id . '_'),
                    )
                    ->extraAttributes(['class' => 'w-32 h-32'])
                    ->acceptedFileTypes(['image/png', 'image/jpeg']),
            ])->columns();
    }

    protected function getLocationDetailsSection(): Component
    {
        return Section::make('Location Details')
            ->schema([
                Select::make('country')
                    ->searchable()
                    ->label('Country')
                    ->live()
                    ->options(Country::getAvailableCountryOptions())
                    ->afterStateUpdated(static function (Set $set) {
                        $set('state', null);
                        $set('city', null);
                    })
                    ->required(),
                Select::make('state')
                    ->label('State / Province')
                    ->searchable()
                    ->live()
                    ->options(static fn (Get $get): mixed => State::getStateOptions($get('country')))
                    ->afterStateUpdated(static fn (Set $set) => $set('city', null))
                    ->nullable(),
                TextInput::make('street_address')
                    ->label('Street Address')
                    ->maxLength(255)
                    ->nullable(),
                Select::make('city')
                    ->label('City / Town')
                    ->searchable()
                    ->options(static fn (Get $get) => City::getCityOptions($get('country'), $get('state')))
                    ->nullable(),
                TextInput::make('postal_code')
                    ->label('Zip / Postal Code')
                    ->maxLength(20)
                    ->nullable(),
            ])->columns();
    }

    protected function getConfigurationSection(): Component
    {
        return Section::make('Configuration Settings')
            ->schema([
                TextInput::make('tax_rate')
                    ->label('Default Tax Rate (%)')
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0)
                    ->maxValue(100)
                    ->required()
                    ->helperText('Enter the tax rate as a percentage (e.g., 10 for 10%).'),
                TextInput::make('default_delivery_fee')
                    ->label('Default Delivery Fee')
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0)
                    ->required(),
            ])->columns(2);
    }

    protected function handleRecordUpdate(CompanyProfile $record, array $data): CompanyProfile
    {
        $record->fill($data);

        $keysToWatch = ['logo'];

        if ($record->isDirty($keysToWatch)) {
            $this->dispatch('companyProfileUpdated');
        }

        $record->save();

        return $record;
    }

    // This method is correctly defined for form actions
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save')
                ->submit('save') // This is crucial for connecting to your save() method
                ->keyBindings(['mod+s']),
        ];
    }

    public static function canView(Model $record): bool
    {
        try {
            return authorize('update', $record)->allowed();
        } catch (AuthorizationException $exception) {
            return $exception->toResponse()->allowed();
        }
    }
}