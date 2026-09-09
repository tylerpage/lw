<?php

namespace Database\Seeders;

use App\Enums\PublishStatus;
use App\Enums\UserRole;
use App\Models\Author;
use App\Models\Capability;
use App\Models\CareerCompany;
use App\Models\CareerRole;
use App\Models\Category;
use App\Models\Discipline;
use App\Models\Industry;
use App\Models\NavigationItem;
use App\Models\NavigationMenu;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (UserRole::cases() as $role) {
            Role::findOrCreate($role->value);
        }

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole(UserRole::SuperAdmin->value);

        $this->seedSiteSettings();
        $this->seedNavigation();
        $this->seedCapabilities();
        $this->seedCareerTimeline();
        $this->seedPages();
        $this->seedProjects();
        $this->seedPosts($admin);
        $this->call(ContentAssistantSeeder::class);
    }

    protected function seedSiteSettings(): void
    {
        $settings = [
            'site_name' => 'Lindsey Wegmann',
            'job_title' => 'Senior Digital Strategist',
            'short_bio' => '[DRAFT] I connect business goals, marketing, operations, and engineering—turning complex commerce challenges into clear, executable plans.',
            'contact_email' => 'hello@example.com',
            'contact_availability' => '[DRAFT] Currently open to senior digital strategy roles and select consulting conversations.',
            'linkedin_url' => 'https://www.linkedin.com/in/lindseywegmann',
            'location' => 'Minneapolis, Minnesota',
            'analytics_enabled' => false,
            'easter_eggs_enabled' => false,
            'default_og_image' => null,
            'resume_path' => null,
            'resume_filename' => 'lindsey-wegmann-resume.pdf',
        ];

        foreach ($settings as $key => $value) {
            SiteSetting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }

    protected function seedNavigation(): void
    {
        $header = NavigationMenu::query()->updateOrCreate(
            ['location' => 'header'],
            ['name' => 'Header Navigation']
        );

        $footer = NavigationMenu::query()->updateOrCreate(
            ['location' => 'footer'],
            ['name' => 'Footer Navigation']
        );

        $headerItems = [
            ['label' => 'Home', 'url' => '/', 'sort_order' => 1],
            ['label' => 'About', 'url' => '/about', 'sort_order' => 2],
            ['label' => 'Work', 'url' => '/work', 'sort_order' => 3],
            ['label' => 'Insights', 'url' => '/insights', 'sort_order' => 4],
            ['label' => 'Contact', 'url' => '/contact', 'sort_order' => 5],
        ];

        NavigationItem::query()->where('navigation_menu_id', $header->id)->delete();
        foreach ($headerItems as $item) {
            NavigationItem::query()->create(array_merge($item, ['navigation_menu_id' => $header->id]));
        }

        NavigationItem::query()->where('navigation_menu_id', $footer->id)->delete();
        foreach ([
            ['label' => 'Privacy', 'url' => '/privacy', 'sort_order' => 1],
            ['label' => 'Contact', 'url' => '/contact', 'sort_order' => 2],
        ] as $item) {
            NavigationItem::query()->create(array_merge($item, ['navigation_menu_id' => $footer->id]));
        }
    }

    protected function seedCapabilities(): void
    {
        Capability::query()->delete();

        $groups = [
            [
                'name' => 'Commerce strategy',
                'items' => [
                    'Ecommerce replatforming',
                    'Platform evaluation',
                    'Digital roadmaps',
                    'B2B and complex catalog commerce',
                ],
            ],
            [
                'name' => 'Technical discovery and solutioning',
                'items' => [
                    'Solution architecture',
                    'Requirements definition',
                    'ERP, PIM, OMS, tax, shipping, POS, and middleware integration planning',
                    'Data and operational workflow mapping',
                ],
            ],
            [
                'name' => 'Omnichannel operations',
                'items' => [
                    'BOPIS',
                    'Multi-location inventory',
                    'POS and fulfillment workflows',
                    'Marketplaces and product feeds',
                ],
            ],
            [
                'name' => 'Growth and customer experience',
                'items' => [
                    'Performance marketing strategy',
                    'Budgeting and forecasting',
                    'SEO-conscious migrations',
                    'Merchandising and conversion experience',
                ],
            ],
            [
                'name' => 'Cross-functional leadership',
                'items' => [
                    'Stakeholder facilitation',
                    'Translating between business and technical teams',
                    'Project lifecycle and delivery planning',
                    'Actionable recommendations and prioritization',
                ],
            ],
        ];

        foreach ($groups as $index => $group) {
            Capability::query()->create([
                'name' => $group['name'],
                'items' => $group['items'],
                'sort_order' => $index + 1,
            ]);
        }
    }

    protected function seedCareerTimeline(): void
    {
        CareerCompany::query()->delete();

        $irishTitan = CareerCompany::query()->create([
            'name' => 'Irish Titan',
            'location' => 'Minneapolis, Minnesota',
            'sort_order' => 1,
        ]);

        $irishRoles = [
            [
                'title' => 'Senior Digital Strategist | Engineering Team',
                'started_at' => '2023-11-01',
                'ended_at' => null,
                'summary' => 'Bridges business stakeholders, marketing teams, and engineering. Leads strategy and solutioning for complex ecommerce builds.',
                'highlights' => [
                    'Replatforming, integrations, solution architecture, and technical discovery',
                    'Omnichannel strategy, B2B commerce, and complex catalogs',
                    'Platform ecosystem includes Shopify/Shopify Plus, BigCommerce, Feedonomics, and Shopware',
                ],
                'sort_order' => 1,
            ],
            [
                'title' => 'Digital Strategist | Performance Marketing',
                'started_at' => '2022-09-01',
                'ended_at' => '2023-11-01',
                'summary' => 'Led paid media and ecommerce performance strategy connected to product data, operations, budgets, and customer experience.',
                'highlights' => [
                    'Google, Meta, Feedonomics, ecommerce marketplaces, and omnichannel experiences',
                ],
                'sort_order' => 2,
            ],
            [
                'title' => 'Digital Project Manager',
                'started_at' => '2021-12-01',
                'ended_at' => '2022-09-01',
                'summary' => 'Led Shopify and BigCommerce ecommerce initiatives with timelines, budgets, and cross-functional alignment.',
                'highlights' => [],
                'sort_order' => 3,
            ],
        ];

        foreach ($irishRoles as $role) {
            CareerRole::query()->create(array_merge($role, ['career_company_id' => $irishTitan->id]));
        }

        $surdyks = CareerCompany::query()->create([
            'name' => "Surdyk's, Inc.",
            'location' => 'Greater Minneapolis–St. Paul Area',
            'sort_order' => 2,
        ]);

        $surdyksRoles = [
            [
                'title' => 'Ecommerce Manager',
                'started_at' => '2020-01-01',
                'ended_at' => '2021-12-01',
                'summary' => 'Oversaw ecommerce operations, order management, reconciliation, and BigCommerce transactional workflows.',
                'highlights' => [
                    'NCR Counterpoint, Annex Cloud, ShipperHQ, Shogun, Constant Contact, Drizly, and Amazon Prime Now',
                    'Managed email promotions, merchandising, and roughly 5,000 SKUs',
                ],
                'sort_order' => 1,
            ],
            [
                'title' => 'Ecommerce Manager: Magento / WordPress',
                'started_at' => '2014-01-01',
                'ended_at' => '2020-01-01',
                'summary' => 'Developed strategy to use WordPress with NCR Online/Magento and managed content, merchandising, and order lifecycle for 5,000+ products.',
                'highlights' => [],
                'sort_order' => 2,
            ],
            [
                'title' => 'Administrator / Website & Office',
                'started_at' => '2007-01-01',
                'ended_at' => '2014-01-01',
                'summary' => "Managed content and products on Surdyk's custom Insite Software ecommerce platform and early social media presence.",
                'highlights' => [
                    'Started and managed early Facebook, Twitter, and Instagram presence from 2009–2013',
                ],
                'sort_order' => 3,
            ],
        ];

        foreach ($surdyksRoles as $role) {
            CareerRole::query()->create(array_merge($role, ['career_company_id' => $surdyks->id]));
        }
    }

    protected function seedPages(): void
    {
        $homeBlocks = [
            [
                'type' => 'hero',
                'enabled' => true,
                'headline' => '[DRAFT] Ecommerce strategy that works in the real world.',
                'subheadline' => '[DRAFT] I connect business goals, marketing, operations, and engineering—turning complex commerce challenges into clear, executable plans.',
                'image' => 'images/lw.jpeg',
                'image_alt' => 'Lindsey Wegmann, Senior Digital Strategist',
                'primary_cta_label' => 'See how I work',
                'primary_cta_url' => '/work',
                'secondary_cta_label' => 'Get in touch',
                'secondary_cta_url' => '/contact',
            ],
            [
                'type' => 'rich_text',
                'enabled' => true,
                'content' => '[DRAFT] Nearly two decades in ecommerce and retail operations and strategy—from operator and manager to project lead, marketer, and strategist.',
            ],
            ['type' => 'capabilities_grid', 'enabled' => true, 'heading' => 'What I do'],
            ['type' => 'featured_projects', 'enabled' => true, 'heading' => 'Featured work', 'limit' => 2],
            ['type' => 'featured_posts', 'enabled' => true, 'heading' => 'Latest insights', 'limit' => 2],
            [
                'type' => 'personality',
                'enabled' => true,
                'status_line' => '[DRAFT] Currently in my strategy-that-ships era.',
                'body' => '[DRAFT] A subtle personality moment—editable in admin.',
            ],
            [
                'type' => 'cta_banner',
                'enabled' => true,
                'heading' => "[DRAFT] Let's talk about your next commerce challenge.",
                'body' => '[DRAFT] Whether you are hiring, collaborating, or exploring a project—I would love to connect.',
                'cta_label' => 'Get in touch',
                'cta_url' => '/contact',
            ],
        ];

        $publishedAt = now()->subDay();

        Page::query()->updateOrCreate(
            ['slug' => 'home'],
            [
                'title' => 'Home',
                'nav_label' => 'Home',
                'status' => PublishStatus::Published,
                'published_at' => $publishedAt,
                'blocks' => $homeBlocks,
                'seo_title' => '[DRAFT] Lindsey Wegmann — Senior Digital Strategist',
                'seo_description' => '[DRAFT] Ecommerce strategy grounded in hands-on operating experience.',
            ]
        );

        Page::query()->updateOrCreate(
            ['slug' => 'about'],
            [
                'title' => 'About',
                'nav_label' => 'About',
                'status' => PublishStatus::Published,
                'published_at' => $publishedAt,
                'blocks' => [
                    [
                        'type' => 'rich_text',
                        'enabled' => true,
                        'content' => "[DRAFT] Lindsey connects business goals, marketing needs, ecommerce operations, and engineering realities—then turns that complexity into a practical plan teams can execute.\n\n[DRAFT] Her recommendations come from having personally managed products, promotions, customers, orders, fulfillment, budgets, and delivery—not just strategy decks.",
                    ],
                    ['type' => 'timeline', 'enabled' => true, 'heading' => 'Experience'],
                    ['type' => 'capabilities_grid', 'enabled' => true, 'heading' => 'Capabilities'],
                    [
                        'type' => 'cta_banner',
                        'enabled' => true,
                        'heading' => '[DRAFT] Download my resume',
                        'cta_label' => 'Download resume',
                        'cta_url' => '/resume',
                    ],
                ],
                'seo_title' => '[DRAFT] About Lindsey Wegmann',
            ]
        );

        Page::query()->updateOrCreate(
            ['slug' => 'contact'],
            [
                'title' => 'Contact',
                'nav_label' => 'Contact',
                'status' => PublishStatus::Published,
                'published_at' => $publishedAt,
                'blocks' => [
                    [
                        'type' => 'rich_text',
                        'enabled' => true,
                        'content' => '[DRAFT] Have a question about ecommerce strategy, a role, or a project? I would love to hear from you.',
                    ],
                ],
                'seo_title' => '[DRAFT] Contact',
            ]
        );

        Page::query()->updateOrCreate(
            ['slug' => 'privacy'],
            [
                'title' => 'Privacy Policy',
                'nav_label' => 'Privacy',
                'status' => PublishStatus::Published,
                'published_at' => $publishedAt,
                'blocks' => [
                    [
                        'type' => 'rich_text',
                        'enabled' => true,
                        'content' => '[DRAFT] Privacy policy placeholder. Final copy to be approved before launch.',
                    ],
                ],
                'seo_title' => '[DRAFT] Privacy Policy',
                'index' => false,
            ]
        );
    }

    protected function seedProjects(): void
    {
        Discipline::query()->updateOrCreate(['slug' => 'commerce-strategy'], ['name' => 'Commerce Strategy']);
        Industry::query()->updateOrCreate(['slug' => 'retail'], ['name' => 'Retail']);

        $projects = [
            [
                'title' => '[DRAFT] Demo Case Study: B2B Catalog Replatform',
                'slug' => 'draft-demo-b2b-catalog-replatform',
                'card_summary' => '[DRAFT] Fictional demo case study for layout and CMS testing only.',
                'client_display_name' => '[DRAFT] Demo Client',
                'role' => '[DRAFT] Lead Digital Strategist',
                'featured' => true,
                'blocks' => [
                    ['type' => 'rich_text', 'enabled' => true, 'content' => '[DRAFT] Overview placeholder for a fictional B2B catalog replatform.'],
                    ['type' => 'rich_text', 'enabled' => true, 'content' => '[DRAFT] The challenge placeholder.'],
                ],
            ],
            [
                'title' => '[DRAFT] Demo Case Study: Omnichannel Fulfillment Planning',
                'slug' => 'draft-demo-omnichannel-fulfillment',
                'card_summary' => '[DRAFT] Fictional demo case study—do not publish as real work.',
                'client_display_name' => '[DRAFT] Demo Client',
                'role' => '[DRAFT] Digital Strategist',
                'featured' => true,
                'blocks' => [
                    ['type' => 'rich_text', 'enabled' => true, 'content' => '[DRAFT] Strategy and workflow mapping placeholder.'],
                ],
            ],
        ];

        foreach ($projects as $data) {
            $project = Project::query()->updateOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, [
                    'status' => PublishStatus::Published,
                    'published_at' => now()->subDay(),
                ])
            );
            $project->disciplines()->sync(Discipline::query()->pluck('id'));
            $project->industries()->sync(Industry::query()->pluck('id'));
        }
    }

    protected function seedPosts(User $admin): void
    {
        $author = Author::query()->updateOrCreate(
            ['slug' => 'lindsey-wegmann'],
            [
                'name' => 'Lindsey Wegmann',
                'bio' => '[DRAFT] Senior Digital Strategist based in Minneapolis.',
                'user_id' => $admin->id,
            ]
        );

        $category = Category::query()->updateOrCreate(
            ['slug' => 'ecommerce-strategy'],
            ['name' => 'Ecommerce Strategy', 'description' => '[DRAFT] Strategy and operations topics.']
        );

        $posts = [
            [
                'title' => '[DRAFT] Demo Post: Questions I Ask Before a Replatform',
                'slug' => 'draft-demo-replatform-questions',
                'excerpt' => '[DRAFT] Fictional demo blog post for CMS and layout testing.',
                'body' => [
                    ['type' => 'rich_text', 'enabled' => true, 'content' => '[DRAFT] Placeholder insight content. Not approved for public launch.'],
                ],
                'featured' => true,
            ],
            [
                'title' => '[DRAFT] Demo Post: Connecting Marketing Plans to Operations',
                'slug' => 'draft-demo-marketing-operations',
                'excerpt' => '[DRAFT] Fictional demo post—clearly labeled draft content.',
                'body' => [
                    ['type' => 'rich_text', 'enabled' => true, 'content' => '[DRAFT] Another placeholder insight for template testing.'],
                ],
                'featured' => false,
            ],
        ];

        foreach ($posts as $data) {
            $post = Post::query()->updateOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, [
                    'status' => PublishStatus::Published,
                    'published_at' => now()->subDay(),
                    'author_id' => $author->id,
                    'reading_time_minutes' => 5,
                ])
            );
            $post->categories()->sync([$category->id]);
        }
    }
}
