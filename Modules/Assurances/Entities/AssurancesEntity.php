<?php

namespace Modules\Assurances\Entities;

use App\Traits\ParamListTrait;
use Modules\Produits\Entities\ProduitsEntity;
/* Remarques importatntes pour cette entity class
    - l'attribut services lors de son enregistrement 
    doit être au format json_encode d'un tableau de doublons:
    [
        'idService' : 2,
        'quantité'  : 3,
    ]
*/

/**
 * Class AssurancesEntity
 *
 * @property int    $idAssurance
 * @property string $nom
 * @property string $images
 * @property string $code
 * @property string $description
 * @property string $short_description
 * @property float  $prix
 * @property string $type
 * @property int    $duree
 * @property string $type_contrat
 * @property string $etat
 * @property string $pieces_a_joindre
 * @property string $assureur
 * @property string $services
 * @property string $reductions
 * @property string $options_de_paiement
 * @property string $formulaire
 */
class AssurancesEntity extends ProduitsEntity
{
    // use ParamListTrait;

    /* Les attributs de la class (Pour reference)
        - idAssurance          (requis)
        - code                 (requis)
        - nom                  (requis)
        - images               (requis)
        - description          (requis)
        - short_description    (requis)
        - prix                 
        - type                 (requis)
        - duree                (requis)
        - type_contrat         
        - etat                 (requis)
        - pieces_a_joindre     
        - assureur             (requis)
        - services        (requis)
        - reductions            
        - options_de_paiement  
        - formulaire           (requis)
    */

    protected $datamap = [
        // property_name => db_column_name
        'idAssurance'      => 'id',
        'categorie'        => 'categorie_id',
        'type'             => 'type_id',
        'typeContrat'      => 'type_contrat',
        'shortDescription' => 'short_description',
        'piecesAJoindre'   => 'pieces_a_joindre',
    ];

    // Defining a type with parameters
    protected $casts = [
        'id'               => "integer",
        'pieces_a_joindre' => "json",
        'etat'             => "etatcaster[Inactif,Actif]",
    ];

    // Bind the type to the handler
    protected $castHandlers = [
        'etatcaster' => \App\Entities\Cast\EtatCaster::class,
    ];

    /**
     * getDocumentation
     * 
     * renvoie la documentation associée à ce produit
     *
     * @return array une liste de documents constituant la documentation
     */
    public function getDocumentation()
    {
        if (!isset($this->attributes['documentation'])) {
            $documentIDs = model('AssuranceDocumentsModel')->asArray()->where('assurance_id', $this->attributes['id'])->findColumn('document_id');
            $documentation = $documentIDs ? model('DocumentsModel')->whereIn('id', $documentIDs)->findAll() : [];
            $this->attributes['documentation'] = $documentation;
        }

        return $this->attributes['documentation'];
    }

    /**
     * getQuestionnaire
     * 
     * renvoie le questionnaire associé à cette assurance
     *
     * @return array une liste de questions constituant le questionnaire
     */
    public function getQuestionnaire()
    {
        if (!isset($this->attributes['questionnaire'])) {
            $questionIDs = model('AssuranceQuestionsModel')->asArray()->where('assurance_id', $this->attributes['id'])->findColumn('question_id');
            $questionnaire = $questionIDs ? model("QuestionsModel")->getBulkQuestionDetails($questionIDs) : [];
            $this->attributes['questionnaire'] = $questionnaire;
        }

        return $this->attributes['questionnaire'];
    }

    /**
     * getCategorie
     * 
     * renvoie la categorie associée à cette assurance
     *
     * @return array une liste de documents constituant la documentation
     */
    public function getCategorieId()
    {
        if (isset($this->attributes['categorie_id']) && gettype($this->attributes['categorie_id']) === 'string') {
            // $cat = model('CategorieProduitsModel')->where('id', $this->attributes['categorie_id'])->first();
            $cat = model('CategorieProduitsModel')->getSimplified($this->attributes['categorie_id']);
            $this->attributes['categorie_id'] = $cat;
        }

        return $this->attributes['categorie_id'] ?? null;
    }

    /**
     * getCategorie
     * 
     * renvoie la categorie associée à cette assurance
     *
     * @return array une liste de documents constituant la documentation
     */
    public function getTypeId()
    {
        if (isset($this->attributes['type_id']) && preg_match('/[integer|string]/i', gettype($this->attributes['type_id']))) {
            $type = model('AssuranceTypesModel')->getSimplified($this->attributes['type_id']);
            $this->attributes['type_id'] = $type;
        }

        return $this->attributes['type_id'] ?? null;
    }

    /**
     * renvoie la liste des services de l'assurance
     *
     * @return array
     */
    public function getServices()
    {
        if (!isset($this->attributes['services'])) {
            $serviceIDs = model("AssuranceServicesModel")->where("assurance_id", $this->attributes['id'])->findColumn("service_id");
            $this->attributes['services'] = $serviceIDs ? model("ServicesModel")->whereIn('id', $serviceIDs)->findAll() : [];
        }
        return $this?->attributes['services'];
    }

    /**
     * renvoie la liste des reductions de l'assurance
     *
     * @return array
     */
    public function getReductions()
    {
        if (!isset($this->attributes['reductions'])) {
            $reductionIDs = model("AssuranceReductionsModel")->where("assurance_id", $this->attributes['id'])->findColumn("reduction_id");
            $this->attributes['reductions'] = $reductionIDs ? model("ReductionsModel")->whereIn('id', $reductionIDs)->findAll() : [];
        }
        return $this?->attributes['reductions'];
    }

    /**
     * renvoie la liste des options de paiement de l'assurance
     *
     * @return array
     */
    public function getPayOptions()
    {
        if (!isset($this->attributes['payOptions'])) {
            $payOptionIDs = model("AssurancePayOptionsModel")->where("assurance_id", $this->attributes['id'])->findColumn("paiement_option_id");
            $this->attributes['payOptions'] = $payOptionIDs ? model("PaiementOptionsModel")->whereIn('id', $payOptionIDs)->findAll() : [];
        }
        return $this?->attributes['payOptions'];
    }

    /**
     * renvoie la liste des images de l'assurance
     *
     * @return array
     */
    public function getImages()
    {
        if (!isset($this->attributes['images'])) {
            $assurImgs = model("AssuranceImagesModel")->where("assurance_id", $this->attributes['id'])->findAll();
            if ($assurImgs) {
                $defaultImgKey = array_search(1, array_column($assurImgs, 'isDefault'));
                $defaultImg = $assurImgs[$defaultImgKey];

                $imgIDs = array_column($assurImgs, 'image_id');
                $images = $imgIDs ? model("ImagesModel")->whereIn('id', $imgIDs)->findAll() : [];

                $this->attributes['images'] = array_map(function ($img) use ($defaultImg) {
                    $img->isDefault = false;
                    if ($img->idImage == $defaultImg['image_id']) {
                        $img->isDefault = true;
                    }
                    return $img;
                }, $images);
            }
        }
        return $this->attributes['images'] ?? null;
    }









    /*
    /**
     * getServices
     * 
     * renvoie la liste des services de l'assurance
     * il s'agit d'un tableau au format ['idService', 'nomService', descriptionService,'quantité']
     *
     * @return array
     *
    public function getServices()
    {
        if (isset($this->attributes['services']) && gettype($this->attributes['services']) === 'string') {
            $dataServices = json_decode($this->attributes['services'], true);

            $servicesIDs  = array_map(function ($val) {
                return $val['idService'];
            }, $dataServices);

            $servicesNames = model('servicesModel')
                ->select('id_service, nomService, descriptionService')
                ->whereIn('id_service', $servicesIDs)
                ->where('etat', ServicesEntity::ACTIF)
                ->findAll();

            $servicesIDs = array_flip($servicesIDs);

            // foreach ($servicesNames as $key => $value) {
            //     $position = $servicesIDs[$value['id_service']];
            //     unset($value['id_service']);
            //     $dataServices[$position] = array_merge($dataServices[$position], $value);
            // }

            foreach ($servicesNames as $service) {
                $position = $servicesIDs[$service->idService];
                $dataServices[$position] = array_merge($dataServices[$position], $service->toArray());
            }

            $this->attributes['services'] = $dataServices;
            return $this->attributes['services'];
        } elseif (isset($this->attributes['services'])) {
            return $this->attributes['services'];
        }

        return null;
    }

    /**
     * getQuestionnaire
     * 
     * renvoie le questionnaire associé à cette assurance
     *
     * @return array une liste de questions constituant le questionnaire
     *
    public function getQuestionnaire()
    {
        if (isset($this->attributes['questions'])) {
            $questionIDs = json_decode($this->attributes['questions'], true);

            $questionnaire = model('QuestionsModel')->getBulkQuestionDetails($questionIDs);

            $this->attributes['questionnaire'] = $questionnaire;
        } else {
            $this->attributes['questionnaire'] = [];
        }

        return $this->attributes['questionnaire'];
    }

    /**
     * @deprecated now using getQuestionnaire
     *
    public function oldGetQuestionnaire()
    {
        if (isset($this->attributes['questions'])) {
            $questionIDs = json_decode($this->attributes['questions'], true);

            $questionnaire = model('QuestionsModel')
                ->whereIn('id_question', $questionIDs)
                ->findAll();

            $questionIDs = array_flip($questionIDs);

            foreach ($questionnaire as $question) {
                $position = $questionIDs[$question->idQuestion];
                $questionnaire[$position] =  $question->toArray();
            }

            $this->attributes['questionnaire'] = $questionnaire;
        } else {
            $this->attributes['questionnaire'] = [];
        }

        return $this->attributes['questionnaire'];
    }

    public function getCategorieId()
    {
        if (isset($this->attributes['categorie_id']) && gettype($this->attributes['categorie_id']) === 'string') {
            $cat = model('CategorieAssurancesModel')->where('id_categorieassurance', $this->attributes['categorie_id'])->first();
            $this->attributes['categorie_id'] = $cat?->toArray();
            return $this->attributes['categorie_id'];
        } elseif (isset($this->attributes['categorie_id'])) {
            return $this->attributes['categorie_id'];
        }
        return null;
    }

    public function getPieceAJoindre()
    {
        if (isset($this->attributes['piece_a_joindre']) && gettype($this->attributes['piece_a_joindre']) === 'string') {
            $piecesIDs = json_decode($this->attributes['piece_a_joindre']);

            $pieces = model('PiecejointesModel')->whereIn('id_piecejointe', $piecesIDs)
                ->findAll();

            $this->attributes['piece_a_joindre'] = array_map(fn ($p) => $p->toArray(), $pieces);
            return $this->attributes['piece_a_joindre'];
        } elseif (isset($this->attributes['piece_a_joindre'])) {
            return $this->attributes['piece_a_joindre'];
        }

        return null;
    }
    */
}
