# OFXtoPHP
## Como utilizar
```php 
include  'ofx_php.php';
$ofx =  new  OFXtoPHP;
```

## Funções
#### Define o arquivo **.ofx** que será utilizado:
```php
$ofx->setFile("arquivo.ofx");
```
> URL do arquivo ou caminho direto.

#### Define as configurações do **OFX**:
```php
$ofx->setConfigs(
	array(
		'closeTags' 	=> true,
		'returnObject' 	=> true
	)
);
```
> closeTags: Fecha as tags do OFX (padrão: true)
> returnObject: Retorna um objeto se for true ou um array  se for false (padrão: true ).

#### Converte o **OFX** em um *array*:
```php
$ofx->convertOfxToArray();
```

#### Obter o dados do banco:
```php
$ofx->getBank();
```
> Obter nome e código do banco que gerou o OFX.

#### Obter datas do **OFX**:
```php
$ofx->getDates();
```
> Obter data de início, data final e data de geração do OFX.

#### Obter dados da conta:
```php
$ofx->getAccount();
```
> Obter número e tipo da conta.

#### Obter as transações: 
```php
$ofx->getTransactions();
```
> Obter o código, tipo, descrição, valor, data da transação e se o valor foi negativo ou positivo.
