<?php

namespace Oxygen\Http\Controllers;

use Oxygen\Core\Controller;
use Oxygen\Core\Model;
use Oxygen\Core\Paginator;
use Oxygen\Core\Flash;

/**
 * Resource Controller Base Class
 * 
 * Provides standard RESTful resource controller methods.
 * 
 * @package    Oxygen\Http\Controllers
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    2.0.0
 */
abstract class ResourceController extends Controller
{
    /**
     * The model class name
     * 
     * @var string
     */
    protected $model;

    /**
     * Display a listing of the resource
     * 
     * @return void
     */
    public function index()
    {
        $model = $this->getModel();
        $items = $model::paginate(15);
        
        $this->view($this->getViewPath('index'), [
            'items' => $items,
            'pagination' => $items
        ]);
    }

    /**
     * Show the form for creating a new resource
     * 
     * @return void
     */
    public function create()
    {
        $this->view($this->getViewPath('create'));
    }

    /**
     * Store a newly created resource
     * 
     * @return void
     */
    public function store()
    {
        $data = $this->getValidatedData();
        $model = $this->getModel();
        
        $item = $model::create($data);
        
        Flash::success($this->getResourceName() . ' created successfully.');
        $this->redirect($this->getResourcePath());
    }

    /**
     * Display the specified resource
     * 
     * @param int $id
     * @return void
     */
    public function show($id)
    {
        $model = $this->getModel();
        $item = $model::find($id);
        
        if (!$item) {
            Flash::error($this->getResourceName() . ' not found.');
            $this->redirect($this->getResourcePath());
            return;
        }
        
        $this->view($this->getViewPath('show'), [
            'item' => $item
        ]);
    }

    /**
     * Show the form for editing the specified resource
     * 
     * @param int $id
     * @return void
     */
    public function edit($id)
    {
        $model = $this->getModel();
        $item = $model::find($id);
        
        if (!$item) {
            Flash::error($this->getResourceName() . ' not found.');
            $this->redirect($this->getResourcePath());
            return;
        }
        
        $this->view($this->getViewPath('edit'), [
            'item' => $item
        ]);
    }

    /**
     * Update the specified resource
     * 
     * @param int $id
     * @return void
     */
    public function update($id)
    {
        $model = $this->getModel();
        $item = $model::find($id);
        
        if (!$item) {
            Flash::error($this->getResourceName() . ' not found.');
            $this->redirect($this->getResourcePath());
            return;
        }
        
        $data = $this->getValidatedData();
        $item->update($data);
        
        Flash::success($this->getResourceName() . ' updated successfully.');
        $this->redirect($this->getResourcePath());
    }

    /**
     * Remove the specified resource
     * 
     * @param int $id
     * @return void
     */
    public function destroy($id)
    {
        $model = $this->getModel();
        $item = $model::find($id);
        
        if (!$item) {
            Flash::error($this->getResourceName() . ' not found.');
            $this->redirect($this->getResourcePath());
            return;
        }
        
        $item->delete();
        
        Flash::success($this->getResourceName() . ' deleted successfully.');
        $this->redirect($this->getResourcePath());
    }

    /**
     * Get the model instance
     * 
     * @return string
     */
    protected function getModel()
    {
        if (!$this->model) {
            throw new \Exception('Model class not defined in controller.');
        }
        
        return $this->model;
    }

    /**
     * Get validated data for create/update
     * 
     * @return array
     */
    protected function getValidatedData()
    {
        return $this->all();
    }

    /**
     * Get the resource name (for flash messages)
     * 
     * @return string
     */
    protected function getResourceName()
    {
        return 'Resource';
    }

    /**
     * Get the resource path (for redirects)
     * 
     * @return string
     */
    protected function getResourcePath()
    {
        return '/';
    }

    /**
     * Get the view path
     * 
     * @param string $view
     * @return string
     */
    protected function getViewPath($view)
    {
        $resourceName = strtolower($this->getResourceName());
        return $resourceName . '/' . $view . '.twig.html';
    }
}

