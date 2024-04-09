# BitcoinKeyGenerator
A PHP library for generating various Bitcoin address formats along with their private and public keys

## Features

- Very light, secure and easy to use
- Generate ECDSA key pairs on the secp256k1 curve
- Retrieve uncompressed and compressed public keys
- Generate private keys in Wallet Import Format (WIF)
- Generate P2PKH, P2SH, P2WPKH, and P2WSH addresses
- Support for Base58Check and Bech32 encoding (SegWit)

## Requirements

- PHP 7.1 or higher
- OpenSSL PHP Extension
- GMP PHP Extension

## Usage

Include the `BtcKeyGen.php` file in your project, create an instance of the BtcKeyGen class and use its methods to generate Bitcoin addresses and keys, like in the following example:

```php
<?php
require_once('BtcKeyGen.php');

$BTC = new BtcKeyGen();

echo "Private key: ".$BTC->getPrivateKey()."\n";
echo "Public key: ".$BTC->getPublicKey()."\n";
echo "Compressed Public Key: ".$BTC->getCompPublicKey()."\n";
echo "WIF: ".$BTC->getWIF()."\n";
echo "Compressed WIF: ".$BTC->getCompWIF()."\n";
echo "P2PKH Address: ".$BTC->getP2PKH()."\n";
echo "Compressed P2PKH Address: ".$BTC->getCompP2PKH()."\n";
echo "P2SH Address: ".$BTC->getP2SH()."\n";
echo "P2WPKH Address: ".$BTC->getP2WPKH()."\n";
echo "P2WSH Address: ".$BTC->getP2WSH()."\n";
?>
```

The above example will output something like this:
```
Private key: 90bae5e94c422b7d8ba8cda13b6f8974e63fa409e909afda2ffb400dd1bc448d
Public key: 04f94c255da6ffb59d1859fa08c2d4b4942b4cf533a6d7ec9eb242047a60f4801ef47fad20a3b143194bca890ab9edb7de80635e3441f95eaaa917f59ae96e63af
Compressed Public Key: 03f94c255da6ffb59d1859fa08c2d4b4942b4cf533a6d7ec9eb242047a60f4801e
WIF: 5Jv2WwACnLoqbuqmY7uix2YW2xWGoXSBS736yTW1vNZSh1J15vt
Compressed WIF: L253h83d18RjX6EFaMVi9sp39dzCkML93oeHNBy54aACPMHyMBS4
P2PKH Address: 1JkQH9oQ8EiCzCAW3D2auwXz7LfKiC1m8o
Compressed P2PKH Address: 1BdyvjvUgdmu1AtmhV3CTGJ9H79SfD4b4P
P2SH Address: 3QAsNPQJBgFs98YjPoDquuE3Bju6T6Jgr2
P2WPKH Address: bc1qwjhupxjkrs2sctfqwm2a4rlhhxna93kk29x7he
P2WSH Address: bc1u7znk6gn992vuzn9u5044rdurwa8yw5a5f5kaacgs555mnnrarvswkuv8v
```

## Contributions

Contributions to **BitcoinKeyGenerator** are highly welcomed and appreciated. Whether it's reporting bugs, suggesting improvements, your input is valuable.

## Help us

If you find this project useful and would like to support its development, consider making a donation. Any contribution is greatly appreciated!

**Bitcoin (BTC) Address:** `3Ctmurhy18PmkTKPa2s7PjfAKzR8ZBj8Na`

## License
**BitcoinKeyGenerator** is licensed under the Apache License, Version 2.0. You are free to use, modify, and distribute the library in compliance with the license.

Copyright (C) 2024 Luca Soltoggio - https://www.lucasoltoggio.it/
