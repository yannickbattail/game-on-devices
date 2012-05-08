#!/bin/bash


function die() {
    echo $1 >&2
    exit 1
}


if [ $# -lt 5 ] ; then
    echo "usage: $0 BASE_URL EMAIL PASSWORD PSEUDO GAME" >&2
    die "wrong parameters."
fi


BASE_URL=$1
EMAIL=$2
PASSWORD=$3
PSEUDO=$4
GAME=$5

wget "$BASE_URL/hello.php?email=$EMAIL&password=$PASSWORD&pseudoInGame=$PSEUDO&game=$GAME" -O god_authKey.tmp -q || die "autentication failed"

AUTH_KEY=$(cat god_authKey.tmp)

while read line ; do
    wget "$BASE_URL/speak.php?authKey=$AUTH_KEY&question=$line" -q -O -
    echo 
done

echo $0: finish ok bye
exit 0

