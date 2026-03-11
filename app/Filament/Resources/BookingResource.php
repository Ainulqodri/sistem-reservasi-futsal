<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Filament\Resources\BookingResource\RelationManagers;
use App\Models\Booking;
use App\Models\Lapangan;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Booking';
    protected static ?string $modelLabel = 'Booking';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('user_id')
                    ->default(fn() => Auth::id())
                    ->required(),

                Hidden::make('order_id')
                    ->default('MANUAL-' . time()),

                Section::make('Detail Jadwal')
                    ->schema([
                        Forms\Components\Select::make('lapangan_id')
                            ->label('Lapangan')
                            ->relationship('lapangan', 'title')
                            ->required()
                            ->live() // Agar saat ganti lapangan, jam reset
                            ->afterStateUpdated(fn(Set $set) => $set('jam_mulai', null)),

                        DatePicker::make('tanggal')
                            ->label('Tanggal Main')
                            ->required()
                            ->native(false)
                            ->displayFormat('d F Y')
                            ->default(now())
                            ->live() // PENTING: Agar trigger update opsi jam
                            ->afterStateUpdated(fn(Set $set) => $set('jam_mulai', null)),
                    ])->columns(2),

                Section::make('Pilih Waktu')
                    ->schema([
                        Select::make('jam_mulai')
                            ->label('Pilih Jam (Bisa Banyak)')
                            ->multiple()
                            ->required()
                            ->options(function (Get $get) {
                                $lapanganId = $get('lapangan_id');
                                $rawTanggal = $get('tanggal');

                                if (!$lapanganId || !$rawTanggal) {
                                    return [];
                                }

                                $tanggal = Carbon::parse($rawTanggal)->format('Y-m-d');

                                $bookedSlots = Booking::where('lapangan_id', $lapanganId)
                                    ->where('tanggal', $tanggal)
                                    ->where('status', '!=', 'cancelled')
                                    ->pluck('jam_mulai')
                                    ->map(fn($time) => Carbon::parse($time)->format('H:i'))
                                    ->toArray();

                                $sekarang = Carbon::now();
                                $isToday = Carbon::parse($tanggal)->isSameDay($sekarang);

                                $options = [];
                                $lapangan = Lapangan::find($lapanganId);

                                for ($i = 7; $i < 24; $i++) {
                                    $jam = sprintf('%02d:00', $i); 
                                    $jamSelesai = sprintf('%02d:00', $i + 1);

                                    if (in_array($jam, $bookedSlots)) {
                                        continue; // Skip (Jangan tampilkan)
                                    }

                                    if ($isToday) {
                                        $waktuSlot = Carbon::parse($tanggal . ' ' . $jam);
                                        if ($waktuSlot->lt($sekarang)) {
                                            continue;
                                        }
                                    }
                                    $harga = $lapangan->getHargaDinamis($tanggal, $jam);
                                    $options[$jam] = "$jam - $jamSelesai (Rp " . number_format($harga) . ")";
                                }
                                return $options;
                            })
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                if (!$state) return;

                                $lapanganId = $get('lapangan_id');
                                $tanggal = $get('tanggal');
                                $lapangan = Lapangan::find($lapanganId);

                                $totalHarga = 0;
                                $jamTerakhir = '00:00';

                                if ($lapangan) {
                                    foreach ($state as $jam) {
                                        $totalHarga += $lapangan->getHargaDinamis($tanggal, $jam);

                                        // Cari jam paling akhir untuk menentukan jam_selesai
                                        if ($jam > $jamTerakhir) {
                                            $jamTerakhir = $jam;
                                        }
                                    }
                                }

                                $set('total_price', $totalHarga);

                                // 2. Set Jam Selesai (Hanya visual saja, ambil jam paling akhir + 1)
                                $jamSelesai = Carbon::parse($jamTerakhir)->addHour()->format('H:i');
                                $set('jam_selesai', $jamSelesai);
                            }),

                        TextInput::make('jam_selesai')
                            ->label('Selesai Pukul')
                            ->readOnly(),

                        TextInput::make('total_price')
                            ->label('Total Harga')
                            ->prefix('Rp')
                            ->numeric()
                            ->readOnly(), // Sebaiknya ReadOnly karena hasil penjumlahan

                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'confirmed' => 'Lunas (Confirmed)',
                                'cancelled' => 'Batal',
                            ])
                            ->default('confirmed')
                            ->required(),
                    ])->columns(2),

                // TextInput::make('user_name')
                //     ->label('Nama Pemesan')
                //     ->required(fn(string $context): bool => $context === 'create')
                //     ->disabled(fn(string $context): bool => $context === 'edit')
                //     ->afterStateHydrated(function ($component, $record) {
                //         $component->state($record?->user?->name ?? $record?->user_name);
                //     }),

                // TextInput::make('user_nomor_telepon')
                //     ->label('Nomor Telepon')
                //     ->required(fn(string $context): bool => $context === 'create')
                //     ->disabled(fn(string $context): bool => $context === 'edit')
                //     ->tel()
                //     ->afterStateHydrated(function ($component, $record) {
                //         $component->state($record?->user?->nomor_telepon ?? $record?->user_nomor_telepon);
                //     }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lapangan.title')
                    ->label('Lapangan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tanggal')
                    ->date()
                    ->sortable(),

                TextColumn::make('jam_mulai')
                    ->time('H:i'),

                TextColumn::make('jam_selesai')
                    ->time('H:i'),

                TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable(),

                TextColumn::make('user.nomor_telepon')
                    ->label('Nomor Telepon')
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                        'completed' => 'info',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'confirmed' => 'Dikonfirmasi',
                        'cancelled' => 'Dibatalkan',
                        'completed' => 'Selesai'
                    })
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
                ])
            ])
            ->defaultSort('created_at', 'desc');
            
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
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}
