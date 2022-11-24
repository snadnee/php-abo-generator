# PHP ABO files generator

This package can be used for generating ABO files which can be used for importing into internet banking. 

## Instalation

## Basic usage
Basic usage of this package is simple. Here is an example.

```php
use Snadnee\ABOGenerator\ABOGenerator;
use Carbon\Carbon;

$senderParams = [
    'bankAccount' => '11-123456789/0600',
    'organisationName' => 'Company xyz',
];

$paymentGroups = [
    'group1' => [
        'dueDate' => Carbon::now(),
        'payments' => [
            [
                'amount' => 1.5,
                'bankAccount' => '112-987654321/3030',
                'variableSymbol' => 1234,
                'specificSymbol' => 4321,
                'constantSymbol' => 1234,
                'message' => 'Payment',
            ],
            [
                'amount' => 1.5,
                'bankAccount' => '112-987654321/3030',
                'variableSymbol' => 1234,
                'specificSymbol' => 4321,
                'constantSymbol' => 1234,
                'message' => 'Payment',
            ],
        ]
    ],
];

$ABOGenerator = new ABOGenerator();

$result = $ABOGenerator->simpleGenerating($senderParams, $paymentGroups);

```

## Advanced usage
The ABO format is separated to these parts: files, groups and payments themselves.

Each file can have its own bank and type (payment, inkaso).
Each group can have its own bank account number in the same bank and its due date (default is today). 
Each payment has an amount, a variable, specific, constant symbol, a message and a bank account.

```php
use Snadnee\ABOGenerator\ABOGenerator;
use Carbon\Carbon;

$ABOGenerator = new ABOGenerator('Company name');

$file = $ABOGenerator->addFile(ABOGenerator::PAYMENT);
$file->setSenderBank(0600);

$group = $file->addGroup();
$group->setSenderAccount(1234567890)
    ->setDate(Carbon::now());

// AccountNumber, amount, variable symbol
$group->addPayment("111-987654321/0300", 14323.43, 2220009813)
    ->setConstantSymbol('8')
    ->setSpecificSymbol('93653')
    ->setMessage('Message');

$group->addPayment("111-2424242424/0300", 1000.5, 2220009813)
    ->setConstantSymbol('8')
    ->setSpecificSymbol('93653')
    ->setMessage('Message');

$file2 = $ABOGenerator->addFile(ABOGenerator::INKASO);
$file2->setSenderBank(0600);

$group = $file2->addGroup();
$group->setSenderAccount(987654321);

$group->addPayment("174-987654321/0300", 1.5, 2220009813)
    ->setConstantSymbol('8')
    ->setSpecificSymbol('93653')
    ->setMessage('Zprava 1');

$group->addPayment("174-987654321/0300", 1234, 2220009813)
    ->setConstantSymbol('8')
    ->setSpecificSymbol('93653')
    ->setMessage('Zprava 2');


$group = $file2->addGroup();
$group->setSenderAccount(227757331);

$group->addPayment("174-987654321/0300", 100, 2220009813)
    ->setConstantSymbol('8')
    ->setSpecificSymbol('93653')
    ->setMessage('Zprava 1');

$group->addPayment("174-987654321/0300", 500, 2220009813)
    ->setConstantSymbol('8')
    ->setSpecificSymbol('93653')
    ->setMessage('Zprava 2');
    
$result = $ABOGenerator->generate();


```
