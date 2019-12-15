<?php
/** 
 * Classe d'accès aux données. 
 
 * Utilise les services de la classe PDO
 * pour l'application GSB
 * Les attributs sont tous statiques,
 * les 4 premiers pour la connexion
 * $monPdo de type PDO 
 * $monPdoGsb qui contiendra l'unique instance de la classe
 
 * @package default
 * @author Cheri Bibi
 * @version    1.0
 * @link       http://www.php.net/manual/fr/book.pdo.php
 */

class PdoGsb{   		
      	private static $serveur='mysql:host=localhost';
      	private static $bdd='dbname=gsbV2';   		
      	private static $user='root' ;    		
      	private static $mdp='' ;	
		private static $monPdo;
		private static $monPdoGsb=null;
/**
 * Constructeur privé, crée l'instance de PDO qui sera sollicitée
 * pour toutes les méthodes de la classe
 */				
	private function __construct(){
    	PdoGsb::$monPdo = new PDO(PdoGsb::$serveur.';'.PdoGsb::$bdd, PdoGsb::$user, PdoGsb::$mdp); 
		if(!isset(PdoGsb::$monPdo)){
			echo 'Probleme de connexion à la bdd';
		}
		else{
			PdoGsb::$monPdo->query("SET CHARACTER SET utf8");
			PdoGsb::$monPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set Errorhandling to 
		}
	}
	public function _destruct(){
		PdoGsb::$monPdo = null;
	}
/**
 * Fonction statique qui crée l'unique instance de la classe
 
 * Appel : $instancePdoGsb = PdoGsb::getPdoGsb();
 
 * @return l'unique objet de la classe PdoGsb
 */
	public  static function getPdoGsb(){
		if(PdoGsb::$monPdoGsb==null){
			PdoGsb::$monPdoGsb= new PdoGsb();
		}
		return PdoGsb::$monPdoGsb;  
	}
/**
 * Retourne les informations d'un visiteur
 
 * @param $login 
 * @param $mdp
 * @return l'id, le nom et le prénom sous la forme d'un tableau associatif 
*/
	//Verification de la fonction sql si elle marche
	public function verif_requete_sql($requete,$execution){
		if(!isset($execution)){
			echo '<br>votre requete sql possede un probleme, 
			essayez de lancer la requete suivant dans la bdd<br>';
			if(!isset($execution)){
				echo '<br>La variable ou se trouve la requete n existe pas<br>';
				die();
			}
			else {
				echo $requete;
				die();
			}
		}
	}

	public function getInfosVisiteur($login, $mdp){
		try {
			$req = "select id, nom, prenom from visiteur 
			where visiteur.login='$login' and visiteur.mdp='$mdp'";
			$rs = PdoGsb::$monPdo->query($req);
			//PdoGsb::verif_requete_sql($req,$rs);
			$ligne = $rs->fetch();
			return $ligne;
		}catch(PDOException $e)
		{
			echo $e->getMessage();
		}
	}

/**
 * Retourne sous forme d'un tableau associatif toutes les lignes de frais hors forfait
 * concernées par les deux arguments
 
 * La boucle foreach ne peut être utilisée ici car on procède
 * à une modification de la structure itérée - transformation du champ date-
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @return tous les champs des lignes de frais hors forfait sous la forme d'un tableau associatif 
*/
	public function getLesFraisHorsForfait($idVisiteur,$mois){
		try {
			$req = "select * from lignefraishorsforfait where lignefraishorsforfait.idvisiteur ='$idVisiteur' 
			and lignefraishorsforfait.mois = '$mois' ";	
			$res = PdoGsb::$monPdo->query($req);
			//PdoGsb::verif_requete_sql($req,$res);
			$lesLignes = $res->fetchAll();
			$nbLignes = count($lesLignes);
			for ($i=0; $i<$nbLignes; $i++){
				$date = $lesLignes[$i]['date'];
				$lesLignes[$i]['date'] =  dateAnglaisVersFrancais($date);
			}
			return $lesLignes; 
		}catch(PDOException $e)
		{
			echo $e->getMessage();
		}
	}
/**
 * Retourne le nombre de justificatif d'un visiteur pour un mois donné
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @return le nombre entier de justificatifs 
*/
	public function getNbjustificatifs($idVisiteur, $mois){
		try {
			$req = "select fichefrais.nbjustificatifs as nb from  fichefrais where fichefrais.idvisiteur ='$idVisiteur' and fichefrais.mois = '$mois'";
			$res = PdoGsb::$monPdo->query($req);
			//PdoGsb::verif_requete_sql($req,$res);
			$laLigne = $res->fetch();
			return $laLigne['nb'];
		}catch(PDOException $e)
		{
			echo $e->getMessage();
		}
	}
/**
 * Retourne sous forme d'un tableau associatif toutes les lignes de frais au forfait
 * concernées par les deux arguments
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @return l'id, le libelle et la quantité sous la forme d'un tableau associatif 
*/
	public function getLesFraisForfait($idVisiteur, $mois){
		try {
			$req = "select fraisforfait.id as idfrais, fraisforfait.libelle as libelle, 
			lignefraisforfait.quantite as quantite from lignefraisforfait inner join fraisforfait 
			on fraisforfait.id = lignefraisforfait.idfraisforfait
			where lignefraisforfait.idvisiteur ='$idVisiteur' and lignefraisforfait.mois='$mois' 
			order by lignefraisforfait.idfraisforfait";	
			$res = PdoGsb::$monPdo->query($req);
			//PdoGsb::verif_requete_sql($req,$res);
			$lesLignes = $res->fetchAll();
			return $lesLignes; 
		}catch(PDOException $e)
		{
			echo $e->getMessage();
		}
	}
/**
 * Retourne tous les id de la table FraisForfait
 
 * @return un tableau associatif 
*/
	public function getLesIdFrais(){
		try {
			$req = "select fraisforfait.id as idfrais from fraisforfait order by fraisforfait.id";
			$res = PdoGsb::$monPdo->query($req);
			//PdoGsb::verif_requete_sql($req,$res);
			$lesLignes = $res->fetchAll();
			return $lesLignes;
		}catch(PDOException $e)
		{
			echo $e->getMessage();
		}
	}
/**
 * Met à jour la table ligneFraisForfait
 
 * Met à jour la table ligneFraisForfait pour un visiteur et
 * un mois donné en enregistrant les nouveaux montants
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @param $lesFrais tableau associatif de clé idFrais et de valeur la quantité pour ce frais
 * @return un tableau associatif 
*/
	public function majFraisForfait($idVisiteur, $mois, $lesFrais){
		try {
			$lesCles = array_keys($lesFrais);
			foreach($lesCles as $unIdFrais){
				$qte = $lesFrais[$unIdFrais];
				$req = "update lignefraisforfait set lignefraisforfait.quantite = $qte
				where lignefraisforfait.idvisiteur = '$idVisiteur' and lignefraisforfait.mois = '$mois'
				and lignefraisforfait.idfraisforfait = '$unIdFrais'";
				$res = PdoGsb::$monPdo->exec($req);
				//PdoGsb::verif_requete_sql($req,$res);
			}
		}catch(PDOException $e)
		{
			echo $e->getMessage();
		}
	}
/**
 * met à jour le nombre de justificatifs de la table ficheFrais
 * pour le mois et le visiteur concerné
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
*/
	public function majNbJustificatifs($idVisiteur, $mois, $nbJustificatifs){
		try {
			$req = "update fichefrais set nbjustificatifs = $nbJustificatifs 
			where fichefrais.idvisiteur = '$idVisiteur' and fichefrais.mois = '$mois'";
			$res = PdoGsb::$monPdo->exec($req);
			//PdoGsb::verif_requete_sql($req,$res);
		}catch(PDOException $e)
		{
			echo $e->getMessage();
		}
	}
/**
 * Teste si un visiteur possède une fiche de frais pour le mois passé en argument
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @return vrai ou faux 
*/	
	public function estPremierFraisMois($idVisiteur,$mois)
	{
		try {
			$ok = false;
			$req = "select count(*) as nblignesfrais from fichefrais 
			where fichefrais.mois = '$mois' and fichefrais.idvisiteur = '$idVisiteur'";
			$res = PdoGsb::$monPdo->query($req);
			//PdoGsb::verif_requete_sql($req,$res);
			$laLigne = $res->fetch();
			if($laLigne['nblignesfrais'] == 0){
				$ok = true;
			}
			return $ok;
		}catch(PDOException $e)
		{
			echo $e->getMessage();
		}
	}
/**
 * Retourne le dernier mois en cours d'un visiteur
 
 * @param $idVisiteur 
 * @return le mois sous la forme aaaamm
*/	
	public function dernierMoisSaisi($idVisiteur){
		try {
			$req = "select max(mois) as dernierMois from fichefrais where fichefrais.idvisiteur = '$idVisiteur'";
			$res = PdoGsb::$monPdo->query($req);
			//PdoGsb::verif_requete_sql($req,$res);
			$laLigne = $res->fetch();
			$dernierMois = $laLigne['dernierMois'];
			return $dernierMois;
		}catch(PDOException $e)
		{
			echo $e->getMessage();
		}
	}
	
/**
 * Crée une nouvelle fiche de frais et les lignes de frais au forfait pour un visiteur et un mois donnés
 
 * récupère le dernier mois en cours de traitement, met à 'CL' son champs idEtat, crée une nouvelle fiche de frais
 * avec un idEtat à 'CR' et crée les lignes de frais forfait de quantités nulles 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
*/
	public function creeNouvellesLignesFrais($idVisiteur,$mois){
		try {
			$dernierMois = $this->dernierMoisSaisi($idVisiteur);
			$laDerniereFiche = $this->getLesInfosFicheFrais($idVisiteur,$dernierMois);
			if($laDerniereFiche['idEtat']=='CR'){
					$this->majEtatFicheFrais($idVisiteur, $dernierMois,'CL');
				
			}
			$req = "insert into fichefrais(idvisiteur,mois,nbJustificatifs,montantValide,dateModif,idEtat) 
			values('$idVisiteur','$mois',0,0,now(),'CR')";
			$res = PdoGsb::$monPdo->exec($req);
			//PdoGsb::verif_requete_sql($req,$res);
			$lesIdFrais = $this->getLesIdFrais();
			foreach($lesIdFrais as $uneLigneIdFrais){
				$unIdFrais = $uneLigneIdFrais['idfrais'];
				$req = "insert into lignefraisforfait(idvisiteur,mois,idFraisForfait,quantite) 
				values('$idVisiteur','$mois','$unIdFrais',0)";
				$rs = PdoGsb::$monPdo->exec($req);
				//PdoGsb::verif_requete_sql($req,$rs);
			 }
		}catch(PDOException $e)
		{
			echo $e->getMessage();
		}
	}
/**
 * Crée un nouveau frais hors forfait pour un visiteur un mois donné
 * à partir des informations fournies en paramètre
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @param $libelle : le libelle du frais
 * @param $date : la date du frais au format français jj//mm/aaaa
 * @param $montant : le montant
*/
	//fonction pour creer une nouvelle fraishorsforfait
	public function creeNouveauFraisHorsForfait($idVisiteur,$mois,$libelle,$date,$montant){
		try {
			$dateFr = dateFrancaisVersAnglais($date);
			$req = "insert into lignefraishorsforfait(idvisiteur,mois,libelle,date,montant) 
			values('$idVisiteur','$mois','$libelle','$dateFr','$montant')";
			$res = PdoGsb::$monPdo->exec($req);
			//PdoGsb::verif_requete_sql($req,$res);
		}catch(PDOException $e)
		{
			echo $e->getMessage();
		}
	}
	//fonction pour modifier un fraishorsforfait il faut recup le numero de ligne du modifier selectionner
		public function majFraisHorsForfait($idVisiteur,$mois,$libelle,$date,$montant){
		try {
			$dateFr = dateFrancaisVersAnglais($date);
			$req = "update lignefraishorsforfait(idvisiteur,mois,libelle,date,montant) 
			set ('$idVisiteur','$mois','$libelle','$dateFr','$montant') Where id='id du ligne'";
			$res = PdoGsb::$monPdo->exec($req);
			//PdoGsb::verif_requete_sql($req,$res);
		}catch(PDOException $e)
		{
			echo $e->getMessage();
		}
	}
/**
 * Supprime le frais hors forfait dont l'id est passé en argument
 
 * @param $idFrais 
*/
	public function supprimerFraisHorsForfait($idFrais){
		try {
			$req = "delete from lignefraishorsforfait where lignefraishorsforfait.id =$idFrais ";
			$res = PdoGsb::$monPdo->exec($req);
			//PdoGsb::verif_requete_sql($req,$res);
		}catch(PDOException $e)
		{
			echo $e->getMessage();
		}
	}
/**
 * Retourne les mois pour lesquel un visiteur a une fiche de frais
 
 * @param $idVisiteur 
 * @return un tableau associatif de clé un mois -aaaamm- et de valeurs l'année et le mois correspondant 
*/
	public function getLesMoisDisponibles($idVisiteur){
		try {
			$req = "select fichefrais.mois as mois from  fichefrais where fichefrais.idvisiteur ='$idVisiteur' 
			order by fichefrais.mois desc ";
			$res = PdoGsb::$monPdo->query($req);
			//PdoGsb::verif_requete_sql($req,$res);
			$lesMois =array();
			$laLigne = $res->fetch();
			while($laLigne != null)	{
				$mois = $laLigne['mois'];
				$numAnnee =substr( $mois,0,4);
				$numMois =substr( $mois,4,2);
				$lesMois["$mois"]=array(
				 "mois"=>"$mois",
				"numAnnee"  => "$numAnnee",
				"numMois"  => "$numMois"
				 );
				$laLigne = $res->fetch(); 		
			}
			return $lesMois;
		}catch(PDOException $e)
		{
			echo $e->getMessage();
		}
	}
/**
 * Retourne les informations d'une fiche de frais d'un visiteur pour un mois donné
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @return un tableau avec des champs de jointure entre une fiche de frais et la ligne d'état 
*/	
	public function getLesInfosFicheFrais($idVisiteur,$mois){
	  try {
		$req = "select ficheFrais.idEtat as idEtat, ficheFrais.dateModif as dateModif, ficheFrais.nbJustificatifs as nbJustificatifs, 
			ficheFrais.montantValide as montantValide, etat.libelle as libEtat from  fichefrais inner join Etat on ficheFrais.idEtat = Etat.id 
			where fichefrais.idvisiteur ='$idVisiteur' and fichefrais.mois = '$mois'";
		$res = PdoGsb::$monPdo->query($req);
		//PdoGsb::verif_requete_sql($req,$res);
		$laLigne = $res->fetch();
		return $laLigne;
	  }catch(PDOException $e)
      {
        echo $e->getMessage();
      }
	}
/**
 * Modifie l'état et la date de modification d'une fiche de frais
 
 * Modifie le champ idEtat et met la date de modif à aujourd'hui
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 */
 
	public function majEtatFicheFrais($idVisiteur,$mois,$etat){
		try {
			$req = "update ficheFrais set idEtat = '$etat', dateModif = now() 
			where fichefrais.idvisiteur ='$idVisiteur' and fichefrais.mois = '$mois'";
			$res = PdoGsb::$monPdo->exec($req);
			//PdoGsb::verif_requete_sql($req,$res);
		}catch(PDOException $e)
        {
        echo $e->getMessage();
        }
	}
	public function affichageComptable($idVisiteur){
	    try {
			$req = "select comptable from visiteur where id ='$idVisiteur'";
			$res = PdoGsb::$monPdo->query($req);
			//PdoGsb::verif_requete_sql($req,$res);
			$comptable_tableau = $res->fetch();
			return $comptable_tableau['comptable'];
	    }catch(PDOException $e)
        {
        echo $e->getMessage();
        }
	}
}
?>