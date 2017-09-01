 	 DROPBEAR=`which dropbear`
        if test -z $DROPBEAR; then
                echo "-> could not find dropbear. Trying to automatically install it ..."
                if [ -f /etc/debian_version ]; then
                        if ! apt-get -y install dropbear; then
                                echo "Failed to install required package dropbear!"
                                echo "Please install dropbear and try again!"
                                return 1
                        fi

                elif [ -f /etc/redhat-release ]; then
                        #cd /tmp

                        # check for rpmforge
                        echo "Checking for rpmforge/DAG repository ..."
                        if rpm -qa | grep rpmforge 1>/dev/null; then
                                echo "-> found rpmforge repository available"
                        else
                                echo "ERROR: Please enable the rpmforge/DAG repository!"
 								return 1
                        fi
                        # check for epel-release
                        echo "Checking for epel-release repository ..."
                        if rpm -qa | grep epel-release 1>/dev/null; then
                                echo "-> found epel-release repository available"
                        else
                            	echo "ERROR: Please enable the epel-release repository!"
                                return 1
                        fi
                        if ! yum -y install dropbear; then
                                echo "Failed to install required package dropbear!"
                                echo "Please install dropbear and try again!"
                                return 1
                        fi
                elif [ -f /etc/SuSE-release ]; then
                        if ! zypper --non-interactive install dropbear; then
                                echo "Failed to install required package dropbear!"
                                echo "Please install dropbear and try again!"
                                return 1
                else
                    	echo "Failed to find package manager to automatically install dropbear."
                        echo "Please install dropbear and try again!"
                        return 1
                fi
        fi
                                
                                
 	# do some extra checks for redhat/centos regarding firewall
        if [ -f /etc/redhat-release ]; then
                # iptables ?
                if which iptables 1>/dev/null; then
                        if iptables -L | grep REJECT 1>/dev/null; then
                                echo "NOTICE: Found iptables firewall enabled!"
                                echo "NOTICE: Inserting rule to allow access to the htvcenter management port $resource_execdport"
                                iptables -I INPUT -m state --state NEW -m tcp -p tcp --dport $resource_execdport -j ACCEPT
                        fi
                fi
        fi

case "$htvcenter_execution_layer" in
 dropbear)
                        echo 'INDROPBEAR'
                        # install and use the distro dropbear package
                        DROPBEAR=`which dropbear`
                        if test -z $DROPBEAR; then

                                FORCE_INSTALL=true htvcenter_install_os_dependency dropbear
                                # on debian and ubuntu, lets make sure it is not started as a service due to our install
                                if test -e /etc/default/dropbear; then

                                        if grep '^NO_START=0' /etc/default/dropbear 1>/dev/null|| ! grep 'NO_START' /etc/default/dropbear 1>/dev/null; then
                                                # looks like it has been set to start by default; let's revert that
                                                /etc/init.d/dropbear stop
                                                sed -i -e "s/^NO_START=0/NO_START=1/g" /etc/default/dropbear
                                                # just in case it was never there in the first place
                                                echo "NO_START=1" >> /etc/default/dropbear
                                        fi
                                fi
                        fi

                        # start dropbear as htvcenter-execd
                        /bin/rm -rf $resource_basedir/htvcenter/etc/dropbear

                        mkdir -p $resource_basedir/htvcenter/etc/dropbear/

                        if ! dropbearkey -t rsa -f $resource_basedir/htvcenter/etc/dropbear/dropbear_rsa_host_key; then
                                echo "ERROR: Could not create host key with dropbearkey. Please check to have dropbear installed correctly!"
                                return 1
                        fi
                        # get the public key of the htvcenter server
                        while (true); do
                                if [ -n "$VALIDIP" ]; then
                                        resource_htvcenterserver="$VALIDIP"
                                fi
                                # !!!! echo "$WGET $htvcenter_web_protocol://$resource_htvcenterserver/htvcenter/boot-service/htvcenter-server-public-rsa-key"
                                if ! $WGET $htvcenter_web_protocol://$resource_htvcenterserver/htvcenter/boot-service/htvcenter-server-public-rsa-key; then
                                        echo "HERE!! 01"
                                        if [ "$START_RETRY" == "$MAX_START_RETRY" ]; then

                                                echo "ERROR: Could not get the public key of the htvcenter-server at $resource_htvcenterserver ! Please check the certific"

                                                return 1
                                        fi

                                        START_RETRY=$(( START_RETRY + 1 ))
                                        sleep 1
                                else
                                    	break
                                fi
                        done


                        if [ ! -d /root/.ssh ]; then
                                mkdir -p /root/.ssh
                                chmod 700 /root/.ssh
                        fi

                        if [ ! -f /root/.ssh/authorized_keys ]; then

                                mv -f htvcenter-server-public-rsa-key /root/.ssh/authorized_keys
                                chmod 600 /root/.ssh/authorized_keys
                        else

                            	htvcenter_HOST=`cat htvcenter-server-public-rsa-key | awk {' print $3 '}`
                                if grep $htvcenter_HOST /root/.ssh/authorized_keys 1>/dev/null; then

                                        sed -i -e "s#.*$htvcenter_HOST.*##g" /root/.ssh/authorized_keys
                                fi

                                cat htvcenter-server-public-rsa-key >> /root/.ssh/authorized_keys
                                rm -f htvcenter-server-public-rsa-key
                                chmod 600 /root/.ssh/authorized_keys
                        fi
                        # start dropbear

                        #echo "dropbear -p $resource_execdport -r $resource_basedir/htvcenter/etc/dropbear/dropbear_rsa_host_key"
                        dropbear -p $resource_execdport -r $resource_basedir/htvcenter/etc/dropbear/dropbear_rsa_host_key
                        ;;
                *)
                  	echo "ERROR: Un-supported command execution layer $htvcenter_execution_layer ! Exiting."
                        return 1
                        ;;
        esac
        
#        
#dbclient -K $DB_TIMEOUT -y -i $htvcenter_SERVER_BASE_DIR/htvcenter/etc/dropbear/dropbear_rsa_host_key -p $htvcenter_EXEC_PORT root@$RESOURCE_IP \" $htvcenter_COMMAND/script\""
#                        