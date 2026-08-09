<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Gcash = 'gcash';
    case BankTransfer = 'bank_transfer';
    case Other = 'other';
}
