<?php
namespace Database\Seeders;
use App\Models\Agent;
use Illuminate\Database\Seeder;

class AgentSeeder extends Seeder {
    public function run(): void {
        $agentsPath = 'C:\\Herd\\claude\\automatyzacja\\.claude\\agents';
        $files = glob($agentsPath . '\\*.md');

        $layerMap = [
            'agent-forger' => ['layer' => 'orchestration', 'color' => '#ef4444'],
            'planner'      => ['layer' => 'planning',      'color' => '#7c3aed'],
            'blueprint'    => ['layer' => 'planning',      'color' => '#7c3aed'],
            'roadmap'      => ['layer' => 'planning',      'color' => '#7c3aed'],
            'coder'        => ['layer' => 'execution',     'color' => '#10b981'],
            'designer'     => ['layer' => 'execution',     'color' => '#10b981'],
            'code-review'  => ['layer' => 'review',        'color' => '#f59e0b'],
            'qa'           => ['layer' => 'review',        'color' => '#f59e0b'],
            'design-qa'    => ['layer' => 'review',        'color' => '#f59e0b'],
            'code-explainer' => ['layer' => 'discovery',   'color' => '#3b82f6'],
            'scribe-worker'  => ['layer' => 'utility',     'color' => '#0891b2'],
        ];

        foreach ($files as $file) {
            $content = file_get_contents($file);
            preg_match('/^---\s*\n(.*?)\n---/s', $content, $matches);
            if (!isset($matches[1])) continue;

            $frontmatter = [];
            foreach (explode("\n", $matches[1]) as $line) {
                if (preg_match('/^(\w+):\s*(.*)$/', trim($line), $m)) {
                    $frontmatter[$m[1]] = trim($m[2], '"\'');
                }
            }

            $slug = $frontmatter['name'] ?? pathinfo($file, PATHINFO_FILENAME);
            $meta = $layerMap[$slug] ?? ['layer' => 'utility', 'color' => '#5e6ad2'];

            Agent::updateOrCreate(['slug' => $slug], [
                'name'        => ucwords(str_replace('-', ' ', $slug)),
                'description' => $frontmatter['description'] ?? null,
                'model'       => $frontmatter['model'] ?? null,
                'layer'       => $meta['layer'],
                'tools'       => isset($frontmatter['tools']) ? array_map('trim', explode(',', $frontmatter['tools'])) : null,
                'file_path'   => $file,
                'color'       => $meta['color'],
                'is_active'   => true,
            ]);
        }
    }
}
