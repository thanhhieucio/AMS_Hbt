# ensure running as root
if [ "$(id -u)" != "0" ]; then
    #Debian doesnt have sudo if root has a password.
    if ! hash sudo 2>/dev/null; then
        exec su -c "$0" "$@"
    else
        exec sudo "$0" "$@"
    fi
fi

wget https://raw.githubusercontent.com/thanhhieucio/HSB-IT/master/hsbit.sh
chmod 744 hsbit.sh
./hsbit.sh 2>&1 | tee -a /var/log/hsbit-install.log
