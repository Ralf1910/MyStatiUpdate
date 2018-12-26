<?
//  Modul mit verschiedenen Hilfsfunktion für Status Updates
//  
//	Version 0.9
//
// ************************************************************
class StatiUpdate extends IPSModule { 
	public function Create() {
		// Diese Zeile nicht löschen.
		parent::Create();
		
	}
	// Berechnung der jeweiligen Jahreswerte
	private function RollierenderJahreswert(Integer $VariableID) {
		//Den Datensatz von vor 365 Tagen abfragen (zur Berücksichtigung von Schaltjahren)
		$historischeWerte = AC_GetLoggedValues($this->ReadPropertyInteger("Archiv"), $VariableID , time()-1000*24*60*60, time()-365*24*60*60, 1);
		$wertVor365d = 0;
		foreach($historischeWerte as $wertVorEinemJahr) {
			$wertVor365d = $wertVorEinemJahr['Value'];
		}
		return (GetValue($VariableID) - $wertVor365d);
	}
	// Aktualisert die Leistungsdaten des Energiezählers
	// 
	// $targetID	= ID der zu akualisierende Variable
	// $sourceID	= ID von wo der Leistungswert bezogen wird
	// $phasen	= Anzahl der Phasen des Zählers (1 oder 3)
	// $ampere	= Maximal mögliche Amperezahl pro Phase
	
	public function SDMLeistung($targetID, $sourceID, $phasen, $ampere)
	{
	
		$lastUpdate		= IPS_GetVariable($sourceID)['VariableUpdated'];
		$maxZeitOhneUpdate	= 120;
		$maxLeistung		= 230*$phasen*$ampere;
		$minLeistung		= -1*$maxLeistung;
	
		if (time() - $lastUpdate > $maxZeitOhneUpdate)
			setValueFloat($targetID, 0);
		else
			setValueFloat($targetID, max(min(Round(getValueFloat($sourceID)), $maxLeistung),$minLeistung));		
	}
	
	// Funktion zur Umrechnung der Helligkeitsweiter des Outdoor Helligkeitsmelder in Lux
	//
	
	public function UpdateHelligkeit($targetID, $sourceID, $sensorType)
	{
		$HM_SEC_MDIR[0]	   = 0;
		$HM_SEC_MDIR[10]   = 0;
		$HM_SEC_MDIR[20]   = 0;
		$HM_SEC_MDIR[30]   = 0;
		$HM_SEC_MDIR[40]   = 3;
		$HM_SEC_MDIR[50]   = 6;
		$HM_SEC_MDIR[60]   = 9;
		$HM_SEC_MDIR[70]   = 13;
		$HM_SEC_MDIR[80]   = 17;
 		$HM_SEC_MDIR[90]   = 20;
 		$HM_SEC_MDIR[100]  = 23;
 		$HM_SEC_MDIR[110]  = 27;
 		$HM_SEC_MDIR[120]  = 30;
 		$HM_SEC_MDIR[130]  = 33;
 		$HM_SEC_MDIR[140]  = 36;
 		$HM_SEC_MDIR[150]  = 39;
 		$HM_SEC_MDIR[160]  = 43;
 		$HM_SEC_MDIR[170]  = 46;
 		$HM_SEC_MDIR[180]  = 50;
 		$HM_SEC_MDIR[190]  = 53;
 		$HM_SEC_MDIR[200]  = 56;
 		$HM_SEC_MDIR[210]  = 60;
 		$HM_SEC_MDIR[220]  = 62;
 		$HM_SEC_MDIR[230]  = 65;
 		$HM_SEC_MDIR[240]  = 67;
 		$HM_SEC_MDIR[250]  = 70;
 		$HM_SEC_MDIR[255]  = 75;
		
		$HM_SEC_MIDR_O[0]  = 0;
		$HM_SEC_MIDR_O[10] = 0;
		$HM_SEC_MIDR_O[20] = 0.013;
		$HM_SEC_MIDR_O[30] = 0.03;
		$HM_SEC_MIDR_O[40] = 0.05;
		$HM_SEC_MIDR_O[50] = 0.1;
		$HM_SEC_MIDR_O[60] = 0.18;
		$HM_SEC_MIDR_O[70] = 0.3;
		$HM_SEC_MIDR_O[80] = 0.52;
		$HM_SEC_MIDR_O[90] = 1;
		$HM_SEC_MIDR_O[100]= 2;
		$HM_SEC_MIDR_O[110]= 3.8;
		$HM_SEC_MIDR_O[120]= 8;
		$HM_SEC_MIDR_O[130]= 13;
		$HM_SEC_MIDR_O[140]= 27;
		$HM_SEC_MIDR_O[150]= 45;
		$HM_SEC_MIDR_O[160]= 90;
		$HM_SEC_MIDR_O[170]= 180;
		$HM_SEC_MIDR_O[180]= 300;
		$HM_SEC_MIDR_O[190]= 600;
		$HM_SEC_MIDR_O[200]= 1000;
		$HM_SEC_MIDR_O[210]= 2100;
		$HM_SEC_MIDR_O[220]= 4000;
		$HM_SEC_MIDR_O[230]= 7500;
		$HM_SEC_MIDR_O[240]= 12000;
		$HM_SEC_MIDR_O[250]= 23000;
		$HM_SEC_MIDR_O[255]= 32000;
 
		$sourceHelligkeit = getValueInteger($sourceID);
        	$targetHelligkeit = -1;
		
		$sourceFloor = floor($sourceHelligkeit/10)*10;
 		$sourceCeil  = min(ceil($sourceHelligkeit/10)*10,255);
 		$sourceFraction = $sourceHelligkeit - $sourceFloor;
		
		if ($sensorType == "HM_SEC_MDIR")
    	if ($sourceFloor == $sourceCeil)
      	$targetHelligkeit = $HM_SEC_MDIR[$sourceFloor];
      else
		   	$targetHelligkeit = ($HM_SEC_MDIR[$sourceCeil] - $HM_SEC_MDIR[$sourceFloor]) / ($sourceCeil - $sourceFloor) * ($sourceFraction)+$HM_SEC_MDIR[$sourceFloor];
		if ($sensorType == "HM_SEC_MDIR_O")
      if ($sourceFloor == $sourceCeil)
      	$targetHelligkeit = $HM_SEC_MDIR_O[$sourceFloor];
      else
		  	$targetHelligkeit = ($HM_SEC_MDIR_O[$sourceCeil] - $HM_SEC_MDIR_O[$sourceFloor]) / ($sourceCeil - $sourceFloor) * ($sourceFraction)+$HM_SEC_MDIR_O[$sourceFloor];
		
	   setValueFloat($targetID, $targetHelligkeit);
	}
 ?>
