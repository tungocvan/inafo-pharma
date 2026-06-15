<?php

namespace Tests\Feature\Category;

use Tests\TestCase;

class CategoryRouteConfigurationTest extends TestCase
{
    public function test_category_routes_preserve_names_and_enforce_permissions(): void
    {
        $routes = app('router')->getRoutes();

        $index = $routes->getByName('admin.category.index');
        $create = $routes->getByName('admin.category.create');
        $edit = $routes->getByName('admin.category.edit');

        $this->assertNotNull($index);
        $this->assertNotNull($create);
        $this->assertNotNull($edit);

        $this->assertSame('admin/category', $index->uri());
        $this->assertSame('admin/category/create', $create->uri());
        $this->assertSame('admin/category/{id}/edit', $edit->uri());

        $this->assertContains('auth:admin', $index->gatherMiddleware());
        $this->assertContains('permission:view_category,admin', $index->gatherMiddleware());
        $this->assertContains('permission:create_category,admin', $create->gatherMiddleware());
        $this->assertContains('permission:edit_category,admin', $edit->gatherMiddleware());
        $this->assertSame('[0-9]+', $edit->wheres['id'] ?? null);
    }
}
