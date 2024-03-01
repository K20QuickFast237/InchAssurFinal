<?php

namespace  Modules\Produits\Controllers;

use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use Modules\Produits\Entities\CategorieProduitsEntity;
use CodeIgniter\Database\Exceptions\DataException;

class CategoriesController extends ResourceController
{
    use ControllerUtilsTrait;
    use ResponseTrait;
    use ErrorsDataTrait;

    protected $helpers = ['Modules\Images\Images'];

    /**
     * Retrieve all product categories records in the database.
     *
     * @return ResponseInterface The HTTP response.
     */
    public function index()
    {
        $response = [
            'status' => 'ok',
            'message' => 'Categories de produits disponibles.',
            'data' => model("CategorieProduitsModel")->select("nom, description, image_id, id")->findAll(),
        ];
        return $this->sendResponse($response);
    }

    /**
     * Retrieve the details of a category
     *
     * @param  int $id - the specified category Identifier
     * @return ResponseInterface The HTTP response.
     */
    public function show($id = null)
    {
        try {
            $data = model("CategorieProduitsModel")->select("nom, description, image_id, id")->where('id', $id)->first();
            $response = [
                'status' => 'ok',
                'message' => 'Détails de la categorie.',
                'data' => $data ?? throw new \Exception("Categorie introuvable"),
            ];
            return $this->sendResponse($response);
        } catch (\Throwable $th) {
            $response = [
                'status' => 'no',
                'message' => 'Categorie introuvable.',
                'data' => [],
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_NOT_ACCEPTABLE);
        }
    }

    /**
     * Creates a new category record in the database.
     *
     * @return ResponseInterface The HTTP response.
     */
    public function create()
    {
        $rules = [
            'nom' => [
                'rules' => 'required|is_unique[categorie_produits.nom]',
                'errors' => [
                    'required' => 'Le nom est requis',
                    'is_unique' => 'Une categorie de ce nom existe déja',
                ]
            ],
            'description' => 'if_exist',
            'image' => [
                'rules' => 'if_exist|max_size[image,100]'
                    . '|mime_in[image,image/png,image/jpg,image/gif]'
                    . '|ext_in[image,png,jpg,gif]|max_dims[image,1024,768]',
                "errors" => [
                    "max_size" => "La taille de l'image est trop grande",
                    "mime_in"  => "Le format de l'image n'est pas valide",
                    "ext_in"   => "Le format de l'image n'est pas valide",
                    "max_dims" => "La taille de l'image est trop grande",
                ]
            ],
        ];
        $input = $this->getRequestInput($this->request);
        $img  = $this->request->getFile('image') ?? null;

        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception('');
            }
            if ($img) {
                $input['image_id'] = saveImage($img, 'uploads/categories/images/');
            }

            // $input['idCategorie'] = model("CategorieProduitsModel")->insert(new CategorieProduitsEntity($input));
            $input['id'] = model("CategorieProduitsModel")->insert($input);
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : "Impossible d'ajouter cette categorie.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $response = [
            'statut'        => 'ok',
            'message'       => 'Categorie ajoutée.',
            'data'          => new CategorieProduitsEntity($input),
        ];
        return $this->sendResponse($response, ResponseInterface::HTTP_CREATED);
    }

    /**
     * Update a category, from "posted" properties
     *
     * @return ResponseInterface The HTTP response.
     */
    public function update($id = null)
    {
        $rules = [
            'nom' => [
                'rules' => 'if_exist|is_unique[categorie_produits.nom]',
                'errors' => [
                    'required' => 'Le nom est requis',
                    'is_unique' => 'Une categorie de ce nom existe déja',
                ]
            ],
            'description' => 'if_exist',
            'image' => [
                'rules' => 'if_exist|max_size[image,100]'
                    . '|mime_in[image,image/png,image/jpg,image/gif]'
                    . '|ext_in[image,png,jpg,gif]|max_dims[image,1024,768]',
                "errors" => [
                    "max_size" => "La taille de l'image est trop grande",
                    "mime_in"  => "Le format de l'image n'est pas valide",
                    "ext_in"   => "Le format de l'image n'est pas valide",
                    "max_dims" => "La taille de l'image est trop grande",
                ]
            ],
        ];

        $input = $this->getRequestInput($this->request);
        $img  = $this->request->getFile('image') ?? null;

        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception('');
            }
            if ($img) {
                $img_id = model("CategorieProduitsModel")->where('id', $id)->findColumn('image_id')[0];
                $input['image_id'] = saveImage($img, 'uploads/categories/images/');
            }
            $input['id'] = $id;
            model("CategorieProduitsModel")->update($id, $input);
            if ($img_id) {
                deleteImage($img_id);
            }
        } catch (DataException $de) {
            $response = [
                'statut'  => 'ok',
                'message' => "Aucune modification apportée.",
            ];
            return $this->sendResponse($response);
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : "Impossible de mettre à jour cette categorie.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $response = [
            'statut'        => 'ok',
            'message'       => 'Categorie mise à jour.',
            'data'          => model("CategorieProduitsModel")->getSimplified($id),
        ];
        return $this->sendResponse($response);
    }

    /**
     * Delete the designated category records in the database
     *
     * @return ResponseInterface The HTTP response.
     */
    public function delete($id = null)
    {
        try {
            model("CategorieProduitsModel")->delete($id);
        } catch (\Throwable $th) {
            $response = [
                'statut'  => 'no',
                'message' => 'Identification de la categorie impossible.',
                'errors'  => $th->getMessage(),
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_NOT_ACCEPTABLE);
        }

        $response = [
            'statut'        => 'ok',
            'message'       => 'Categorie supprimée.',
            'data'          => [],
        ];
        return $this->sendResponse($response);
    }

    /**
     * Retrieve the all MarketPlace categories
     *
     * @return ResponseInterface The HTTP response.
     */
    public function getAllSubCat()
    {
        $response = [
            'status' => 'ok',
            'message' => 'Sous-categories disponibles.',
            'data' => model("MkpCategorieProduitsModel")->findAll(),
        ];
        return $this->sendResponse($response);
    }

    /**
     * Add a MarketPlace category
     *
     * @return ResponseInterface The HTTP response.
     */
    public function addSubcat()
    {
        $rules = [
            'nom' => [
                'rules' => 'required|is_unique[categorie_mkps.nom]',
                'errors' => [
                    'required' => 'Le nom est requis',
                    'is_unique' => 'Une sous-catégorie de ce nom existe déja',
                ]
            ],
            'description' => 'if_exist',
            'image' => [
                'rules' => 'if_exist|max_size[image,100]'
                    . '|mime_in[image,image/png,image/jpg,image/gif]'
                    . '|ext_in[image,png,jpg,gif]|max_dims[image,1024,768]',
                "errors" => [
                    "max_size" => "La taille de l'image est trop grande",
                    "mime_in"  => "Le format de l'image n'est pas valide",
                    "ext_in"   => "Le format de l'image n'est pas valide",
                    "max_dims" => "La taille de l'image est trop grande",
                ]
            ],
        ];
        $input = $this->getRequestInput($this->request);
        $img   = $this->request->getFile('image') ?? null;

        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception('');
            }
            if ($img) {
                $input['image_id'] = saveImage($img, 'uploads/categories/images/');
            }

            // $input['idCategorie'] = model("CategorieProduitsModel")->insert(new CategorieProduitsEntity($input));
            $input['id'] = model("MkpCategorieProduitsModel")->insert($input);
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : "Impossible d'ajouter cette categorie.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $response = [
            'statut'  => 'ok',
            'message' => 'Sous-categorie ajoutée.',
            'data'    => new CategorieProduitsEntity($input),
        ];
        return $this->sendResponse($response, ResponseInterface::HTTP_CREATED);
    }

    /**
     * Retrieve the details of a MarketPlace category
     *
     * @param  int $id - the specified category Identifier
     * @return ResponseInterface The HTTP response.
     */
    public function showSubcat($id = null)
    {
        try {
            $data = model("MkpCategorieProduitsModel")->where('id', $id)->first();
            $response = [
                'status' => 'ok',
                'message' => 'Détails de la Sous-categorie.',
                'data' => $data ?? throw new \Exception("Sous-categorie introuvable"),
            ];
            return $this->sendResponse($response);
        } catch (\Throwable $th) {
            $response = [
                'status' => 'no',
                'message' => 'Sous-categorie introuvable.',
                'data' => [],
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_NOT_ACCEPTABLE);
        }
    }

    /*
     * Retrieves the list of states.
     *
     * @return ResponseInterface The HTTP response.
     *
        public function getStates()
        {
            $entity = new ServicesEntity();
            $response = [
                'status' => 'ok',
                'message' => 'Status de services.',
                'data' => $entity->statesList,
            ];
            return $this->sendResponse($response);
        }
    */
}
