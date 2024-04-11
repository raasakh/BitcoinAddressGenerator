<?php
require_once('BitcoinAddressGenerator.php');

$BTC = new BitcoinAddressGenerator();

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
echo "\n";

$BTC->newKeys("a strong password");

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
echo "\n";

$BTC->newKeys("b428729db6df4dd1b14e20887d3f9cd71486f1c39ed994c065b17b5eb2f7e4a7");

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

/*
This example will output something like this:
Private key: 1bed9889bfba4cc2088789b4de51a85879d7bea9297ee2e815219b84e3ff5ec2
Public key: 04b8a88d781ac35bf0361f4e17f12ca5b47ff7d2f83c15e06e8a92e4aaf91a100df5b57eec4a99bfc849e282088241883538376c5461bbaff57cf3aa045a5ca453
Compressed Public Key: 03b8a88d781ac35bf0361f4e17f12ca5b47ff7d2f83c15e06e8a92e4aaf91a100d
WIF: 5J2ay59C4n8gNybDN2APXRuMmYJecWPQfaHufLq5zKSLFsoGVmd
Compressed WIF: Kx9zvVH9qgCtozn1Zn8SRJv6fbAu1ihuTFwdSEXF1zNozQYpJU3r
P2PKH Address: 1LWwyDEYLT2tW2923DiA7sG7S5MrLACBSG
Compressed P2PKH Address: 197UuDnpdJb8NvmV9EhU5Epa7KtgkP8v5e
P2SH Address: 37v83Kri5CzYih7b9NcUhfpK8p2VopDnt3
P2WPKH Address: bc1qtrav4306ragl78646qrnjey6w3wag9zkx87929
P2WSH Address: bc1hlya7d0lpxj32kzez6ckldyk5np4r8265k2fksyc6hsfy0aj3nhqp4zfr0

Private key: d38d2f12d3af5393efac2f80a69e35782cba39ce1f872acc86af74443235975e
Public key: 04af55aa57b4e6f37e29a349153ca75fcfe735828230fbc5a4a849668c78cff6fee283889aced66b484fcb6d325db92f0c87d0a9ee64e77acc57cf15eb70f4b2b8
Compressed Public Key: 02af55aa57b4e6f37e29a349153ca75fcfe735828230fbc5a4a849668c78cff6fe
WIF: 5KRTP2hfotcxAWNC4sFTod6JZcYSiYHSw574WY5ocDVeEc9H9G1
Compressed WIF: L4JwSEWWHxqgUxcXr4nqJnJQWwsnWM5bTf7kVNCCiS9RLtMHwmQ8
P2PKH Address: 1MvMGQPafLnuhM2dZSKEGeniS6nsp7LUfC
Compressed P2PKH Address: 1JQUDZBKj74T256eaDSNpFWkxxHhtx3ANr
P2SH Address: 3JykFqTQqUknATNJ5XT258yBmLvG95nv7f
P2WPKH Address: bc1qhm4xevknjxlp0uat0pla8g78k0zze33dgkysdz
P2WSH Address: bc1reasqnewh3uzrs20kn9hr6kxam2e5py58k62q3tahzwatnsv6uequl02r4

Private key: b428729db6df4dd1b14e20887d3f9cd71486f1c39ed994c065b17b5eb2f7e4a7
Public key: 043acb033826e2c3018fb88bc0e8a192f926cf3b2a73df2a00c7d45b5b522a47cd9953c59826bb217464d95cd9c0fbe4f1306b09ef526dabd55a8452c64a9d04cf
Compressed Public Key: 033acb033826e2c3018fb88bc0e8a192f926cf3b2a73df2a00c7d45b5b522a47cd
WIF: 5KBdUYphQaPwmcsvssq3JBqFGnVCAM563qDFNfpGqyz8JeKPBSC
Compressed WIF: L3FuzbTXM4QGjwMnoZM38rowzfmSGFb3HVo4SeKewCX9Ei5VhEFM
P2PKH Address: 1P2iFYGFB6GwguBfLEaqo2hSz6V9N5261J
Compressed P2PKH Address: 1E1P4noxSdNpErNwRHFzMSPucgmeDsHi4p
P2SH Address: 3DxLbTj5ARZ33VQ6yvccaEqWVVkEsdj3cz
P2WPKH Address: bc1q36kqmmduxne6fzse362e2ms7xxcmycw8uh4gft
P2WSH Address: bc1qx8hepy0nqq5r76jm5grqf6ash2hlydhe5upuw4gzsc5tpnxxxsq522h3u
*/
?>
