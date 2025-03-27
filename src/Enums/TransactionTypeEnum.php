<?php

namespace MarJose123\Pitaka\Enums;

enum TransactionTypeEnum: string
{
    case PAYMENT = 'Payment';
    case REFUND = 'Refund';
    case DEPOSIT = 'Deposit';
    case FEE = 'Fee';
    case EXCHANGE = 'Exchange';
    case TRANSFER = 'Transfer';
}
