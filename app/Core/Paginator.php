<?php

namespace Oxygen\Core;

/**
 * Paginator - Simple pagination system
 * 
 * Handles pagination of database results.
 * 
 * @package    Oxygen\Core
 */
class Paginator
{
    protected $items;
    protected $total;
    protected $perPage;
    protected $currentPage;
    protected $lastPage;

    public function __construct($items, $total, $perPage = 15, $currentPage = 1)
    {
        $this->items = $items;
        $this->total = $total;
        $this->perPage = $perPage;
        $this->currentPage = $currentPage;
        $this->lastPage = max((int) ceil($total / $perPage), 1);
    }

    /**
     * Get the items for the current page
     */
    public function items()
    {
        return $this->items;
    }

    /**
     * Get the total number of items
     */
    public function total()
    {
        return $this->total;
    }

    /**
     * Get the number of items per page
     */
    public function perPage()
    {
        return $this->perPage;
    }

    /**
     * Get the current page number
     */
    public function currentPage()
    {
        return $this->currentPage;
    }

    /**
     * Get the last page number
     */
    public function lastPage()
    {
        return $this->lastPage;
    }

    /**
     * Check if there are more pages
     */
    public function hasMorePages()
    {
        return $this->currentPage < $this->lastPage;
    }

    /**
     * Get the URL for a given page
     */
    public function url($page)
    {
        $query = $_GET;
        $query['page'] = $page;
        return '?' . http_build_query($query);
    }

    /**
     * Get pagination links HTML
     */
    public function links()
    {
        if ($this->lastPage <= 1) {
            return '';
        }

        $html = '<nav class="flex items-center justify-between border-t border-gray-200 px-4 sm:px-0 mt-6">';
        $html .= '<div class="flex w-0 flex-1">';

        // Previous link
        if ($this->currentPage > 1) {
            $html .= '<a href="' . $this->url($this->currentPage - 1) . '" class="inline-flex items-center border-t-2 border-transparent pr-1 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">';
            $html .= '← Previous</a>';
        }

        $html .= '</div>';

        // Page numbers
        $html .= '<div class="hidden md:flex">';
        for ($i = 1; $i <= $this->lastPage; $i++) {
            if ($i == $this->currentPage) {
                $html .= '<a href="' . $this->url($i) . '" class="inline-flex items-center border-t-2 border-blue-500 px-4 pt-4 text-sm font-medium text-blue-600">' . $i . '</a>';
            } else {
                $html .= '<a href="' . $this->url($i) . '" class="inline-flex items-center border-t-2 border-transparent px-4 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">' . $i . '</a>';
            }
        }
        $html .= '</div>';

        // Next link
        $html .= '<div class="flex w-0 flex-1 justify-end">';
        if ($this->hasMorePages()) {
            $html .= '<a href="' . $this->url($this->currentPage + 1) . '" class="inline-flex items-center border-t-2 border-transparent pl-1 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">';
            $html .= 'Next →</a>';
        }
        $html .= '</div>';

        $html .= '</nav>';

        return $html;
    }

    /**
     * Convert to array
     */
    public function toArray()
    {
        return [
            'data' => $this->items,
            'current_page' => $this->currentPage,
            'last_page' => $this->lastPage,
            'per_page' => $this->perPage,
            'total' => $this->total,
        ];
    }
}
