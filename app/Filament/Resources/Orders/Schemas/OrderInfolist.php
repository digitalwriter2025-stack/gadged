<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

/**
 * Order detail: a summary strip on top, items, totals and timeline on the left;
 * customer and delivery details on the right.
 */
class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'lg' => 3])
            ->components([
                ViewEntry::make('summary')
                    ->hiddenLabel()
                    ->view('filament.orders.summary')
                    ->columnSpanFull(),

                Group::make([
                    Section::make('Items')
                        ->icon('heroicon-o-shopping-bag')
                        ->schema([
                            ViewEntry::make('items')
                                ->hiddenLabel()
                                ->view('filament.orders.items'),

                            Group::make([
                                TextEntry::make('subtotal')
                                    ->money('BDT')
                                    ->inlineLabel(),
                                TextEntry::make('discount_total')
                                    ->label('Discount')
                                    ->color('success')
                                    ->formatStateUsing(fn (string $state): string => '− BDT '.number_format((float) $state, 2))
                                    ->visible(fn (Order $record): bool => (float) $record->discount_total > 0)
                                    ->inlineLabel(),
                                TextEntry::make('shipping_total')
                                    ->label('Delivery charge')
                                    ->money('BDT')
                                    ->inlineLabel(),
                                TextEntry::make('grand_total')
                                    ->label('Grand total')
                                    ->money('BDT')
                                    ->size('lg')
                                    ->weight(FontWeight::Bold)
                                    ->color('primary')
                                    ->extraEntryWrapperAttributes(['class' => 'gx-order-grand-total'])
                                    ->inlineLabel(),
                            ])->extraAttributes(['class' => 'gx-order-totals']),
                        ]),

                    Section::make('Timeline')
                        ->icon('heroicon-o-clock')
                        ->schema([
                            ViewEntry::make('timeline')
                                ->hiddenLabel()
                                ->view('filament.orders.timeline'),
                        ]),
                ])->columnSpan(['lg' => 2]),

                Group::make([
                    Section::make('Customer')
                        ->icon('heroicon-o-user')
                        ->schema([
                            TextEntry::make('customer_name')->label('Name')->weight(FontWeight::SemiBold)->inlineLabel(),
                            TextEntry::make('customer_phone')->label('Phone')->copyable()->icon('heroicon-m-phone')->inlineLabel(),
                            TextEntry::make('customer_email')->label('Email')->copyable()->placeholder('—')->inlineLabel(),
                            TextEntry::make('customer.name')->label('Account')->placeholder('Guest checkout')->inlineLabel(),
                        ]),

                    Section::make('Delivery')
                        ->icon('heroicon-o-map-pin')
                        ->schema([
                            TextEntry::make('shipping_address')->label('Address'),
                            TextEntry::make('shipping_area')->label('Area')->placeholder('—')->inlineLabel(),
                            TextEntry::make('shipping_district')->label('District')->inlineLabel(),
                            TextEntry::make('shipping_method_name')->label('Method')->placeholder('—')->inlineLabel(),
                            TextEntry::make('courier_name')->label('Courier')->placeholder('Not assigned')->inlineLabel(),
                            TextEntry::make('tracking_number')->label('Tracking')->copyable()->placeholder('—')->inlineLabel(),
                        ]),

                    Section::make('Customer note')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->visible(fn (Order $record): bool => filled($record->customer_note))
                        ->schema([
                            TextEntry::make('customer_note')->hiddenLabel(),
                        ]),
                ])->extraAttributes(['class' => 'gx-order-side']),
            ]);
    }
}
