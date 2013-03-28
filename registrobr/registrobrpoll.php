<?
# Copyright (c) 2013, NIC.br (R)
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#

# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.


# Official Website for whmcs-registrobr-epp
# https://github.com/registrobr/whmcs-registrobr-epp


# More information on NIC.br(R) domain registration services, Registro.br(TM), can be found at http://registro.br
# Information for registrars available at http://registro.br/provedor/epp

# NIC.br(R) is a not-for-profit organization dedicated to domain registrations and fostering of the Internet in Brazil. No WHMCS services of any kind are available from NIC.br(R).


# Include Registro.br stuff we need
require_once dirname(__FILE__) . '/registrobr.php';



echo "Registro.br Poll" ;

$i=0;
# Loop with each one
while($i < 100) { // prevent  inbox flooding
	sleep(1);
	registrobr_Poll();
	
	$i++;
}


?>
