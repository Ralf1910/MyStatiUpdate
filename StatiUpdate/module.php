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
	
	
 }
 ?>
