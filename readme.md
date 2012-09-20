# [Pagination Module](https://github.com/nexeck/kohana-pagination) for the Kohana framework

## Features
* Supports [Twitter Bootstrap](http://twitter.github.com/bootstrap/)
* Supports Twig template engine [Twig Kohana module](https://github.com/nexeck/kohana-twig)

---
## Installation

Add the submodule:

    git submodule add git://github.com/nexeck/kohana-pagination.git modules/pagination

**Note:** [Guide for Kohana modules](http://kohanaframework.org/3.2/guide/kohana/modules)

### Kohana Modules
    Kohana::modules(array(
        ...
        'pagination' => MODPATH . 'pagination',

---
## Usage

### Create Bootstrap Navbar Top

    $pagination = Pagination::factory(100, 20);
    $this->template->set('pagination', $pagination);

#### Include in twig template

    {% include 'pagination/bootstrap.twig' %}
