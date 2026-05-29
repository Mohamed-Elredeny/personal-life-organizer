<?php

namespace App\Filament\Pages;

use App\Support\Money;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->columns(2)
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                    ]),
                Forms\Components\Section::make(__('Preferences'))
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('locale')
                            ->label(__('Language'))
                            ->options(['en' => 'English', 'ar' => 'العربية'])
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('default_currency')
                            ->label(__('Default currency'))
                            ->options(Money::options())
                            ->required()
                            ->native(false),
                    ]),
                Forms\Components\Section::make()
                    ->columns(2)
                    ->schema([
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ]),
            ]);
    }

    protected function afterSave(): void
    {
        session()->forget('locale');
    }
}
