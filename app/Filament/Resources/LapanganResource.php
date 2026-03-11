<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LapanganResource\Pages;
use App\Filament\Resources\LapanganResource\RelationManagers;
use App\Models\Lapangan;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Nette\Utils\ImageColor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;

class LapanganResource extends Resource
{
    protected static ?string $model = Lapangan::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $slug = 'lapangan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // --- BAGIAN 1: INFORMASI UMUM ---
                Section::make('Informasi Lapangan')
                    ->description('Detail utama lapangan.')
                    ->schema([
                        TextInput::make('title')
                            ->label('Nama Lapangan')
                            ->required()
                            ->placeholder('Contoh: Lapangan Timur (Vinyl)')
                            ->columnSpanFull(),

                        Select::make('category')
                            ->label('Kategori')
                            ->options([
                                'standard' => 'Standard',
                                'unggulan' => 'Unggulan'
                            ])
                            ->required(),

                        Toggle::make('status')
                            ->label('Status Aktif')
                            ->default(true)
                            ->inline(false),
                    ])->columns(2),

                // --- BAGIAN 2: PENGATURAN HARGA DINAMIS ---
                Section::make('Pengaturan Harga Sewa')
                    ->description('Atur harga berbeda untuk Pagi (07:00-15:00) dan Malam (15:00-24:00).')
                    ->schema([

                        // Kolom Kiri: Weekday
                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                Forms\Components\Placeholder::make('label_weekday')
                                    ->label('Senin - Jumat')
                                    ->content('Harga untuk hari kerja biasa.'),

                                TextInput::make('price') // Kolom database: price
                                    ->label('Pagi (07:00 - 15:00)')
                                    ->helperText('Harga dasar.')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->step(1000)
                                    ->required(),

                                TextInput::make('price_weekday_night') // Kolom database baru
                                    ->label('Malam (15:00 - 24:00)')
                                    ->helperText('Kosongkan jika sama dengan harga weekend malam.')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->step(1000),
                            ]),

                        // Kolom Kanan: Weekend
                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                Forms\Components\Placeholder::make('label_weekend')
                                    ->label('Sabtu & Minggu')
                                    ->content('Harga untuk akhir pekan.'),

                                TextInput::make('price_weekend_day') // Kolom database baru
                                    ->label('Pagi (07:00 - 15:00)')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->step(1000),

                                TextInput::make('price_weekend_night') // Kolom database baru
                                    ->label('Malam (15:00 - 24:00)')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->step(1000),
                            ]),
                    ])->columns(2), // Membagi layout menjadi 2 kolom besar

                // --- BAGIAN 3: DETAIL & GAMBAR ---
                Section::make('Detail Visual')
                    ->schema([
                        RichEditor::make('description')
                            ->label('Deskripsi Lengkap')
                            ->required()
                            ->columnSpanFull(),

                        FileUpload::make('images')
                            ->label('Gambar Lapangan')
                            ->image()
                            ->multiple()
                            ->maxFiles(3)
                            ->required()
                            ->directory('lapangan-images')
                            ->columnSpanFull()
                            ->helperText('Maksimal 3 gambar. Format: JPG, PNG.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Nama Lapangan')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'unggulan' => 'warning',
                        'standard' => 'gray',
                    }),

                TextColumn::make('price')
                    ->label('Harga Dasar (Pagi)')
                    ->sortable()
                    ->money('IDR'),

                // Kita sembunyikan harga detail lainnya dari tabel utama agar rapi
                // Admin bisa melihat detailnya saat klik "Edit"

                ImageColumn::make('images')
                    ->label('Foto')
                    ->circular()
                    ->stacked()
                    ->limit(2),

                IconColumn::make('status')
                    ->boolean(),
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
            'index' => Pages\ListLapangans::route('/'),
            'create' => Pages\CreateLapangan::route('/create'),
            'edit' => Pages\EditLapangan::route('/{record}/edit'),
        ];
    }
}
