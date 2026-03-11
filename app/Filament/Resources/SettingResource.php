<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Filament\Resources\SettingResource\RelationManagers;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Pengaturan';
    protected static ?string $modelLabel = 'Pengaturan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('key')
                    ->label('Nama Pengaturan')
                    ->required()
                    ->unique(ignoreRecord: true)
                    // HANYA ReadOnly jika sedang mode EDIT.
                    // Jika mode CREATE, ini bisa diisi.
                    ->readOnly(fn(string $operation) => $operation === 'edit'),

                // 1. Input Biasa (Text)
                TextInput::make('value')
                    ->label('Nilai')
                    ->required()
                    // Sembunyikan jika ini jam atau libur
                    ->hidden(fn($record) => $record && (
                        in_array($record->key, ['jam_buka', 'jam_tutup']) ||
                        str_contains($record->key, 'libur')
                    )),

                // 2. Input Waktu (Untuk Jam Operasional)
                TimePicker::make('value')
                    ->label('Waktu')
                    ->required()
                    ->seconds(false)
                    ->format('H:i') // Format simpan ke DB
                    ->displayFormat('H:i') // Format tampilan
                    ->visible(fn($record) => $record && in_array($record->key, ['jam_buka', 'jam_tutup'])),

                // 3. Input Tanggal (KHUSUS HARI LIBUR) - BARU
                DatePicker::make('value')
                    ->label('Tanggal Libur')
                    ->required()
                    ->native(false)
                    ->displayFormat('d F Y')
                    ->format('Y-m-d') // Simpan format standar SQL
                    // Tampil HANYA jika key mengandung kata 'libur'
                    ->visible(fn($record) => $record && str_contains($record->key, 'libur')),

                Textarea::make('description')
                    ->label('Keterangan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label('Pengaturan')
                    ->formatStateUsing(fn(string $state): string => str($state)->title()->replace('_', ' ')), // Biar "jam_buka" jadi "Jam Buka"

                TextColumn::make('value')
                    ->label('Nilai')
                    ->formatStateUsing(function (string $state, $record): string {
                        // Format tampilan Jam
                        if (in_array($record->key, ['jam_buka', 'jam_tutup'])) {
                            return date('H:i', strtotime($state));
                        }
                        // Format tampilan Tanggal Libur
                        if (str_contains($record->key, 'libur')) {
                            return \Carbon\Carbon::parse($state)->translatedFormat('d F Y');
                        }
                        return $state;
                    }),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
