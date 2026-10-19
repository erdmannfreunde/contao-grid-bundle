<?php

declare(strict_types=1);

/*
 * This file is part of erdmannfreunde/contao-grid-bundle.
 *
 * (c) Erdmann & Freunde <https://erdmann-freunde.de>
 *
 * @license MIT
 */

namespace ErdmannFreunde\ContaoGridBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Fragment\Reference\ContentElementReference;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\StringUtil;
use ErdmannFreunde\ContaoGridBundle\Grid\SpanParser;
use ErdmannFreunde\ContaoGridBundle\GridClasses;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement('grid', category: 'euf_grid', nestedFragments: true)]
class GridController extends AbstractContentElementController
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly GridClasses $gridClasses,
        private readonly SpanParser $spanParser,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        // Im Backend bedeuten die Klassen nichts — dort steht eine Schemaansicht,
        // und die braucht die Spannweiten als Zahlen
        $imEditor = (bool) $template->get('as_editor_view');
        $zellen = [];

        foreach ($template->get('nested_fragments') as $reference) {
            $kind = $this->resolveModel($reference);

            if (!$kind instanceof ContentModel) {
                continue;
            }

            $zelle = [
                'reference' => $reference,
                'classes' => $this->cellClasses($kind),
                'type' => $kind->type,
                'headline' => $this->headline($kind),
            ];

            if ($imEditor) {
                // Im Backend steht die Beschriftung des Elementtyps, so wie in
                // der Inhaltsliste daneben
                $zelle['type'] = (string) ($GLOBALS['TL_LANG']['CTE'][$kind->type][0] ?? $kind->type);
                $zelle['span'] = $this->span($kind);
            }

            $zellen[] = $zelle;
        }

        $template->set('cells', $zellen);
        $template->set('columns', \count($this->gridClasses->getColumns()));

        return $template->getResponse();
    }

    /**
     * Die Spannweite, die am größten Viewport gilt — dieselbe Lesart wie in der
     * Inhaltsliste, damit Übersicht und Liste dasselbe zeigen.
     *
     * @return array<string, int>
     */
    private function span(ContentModel $kind): array
    {
        $viewports = $this->gridClasses->getViewports();
        $viewport = (string) (end($viewports) ?: 'md');
        $geometrie = $this->spanParser->parse($kind->grid_columns, $kind->grid_options);

        return [
            'col' => $this->spanParser->effective($geometrie['col'], $viewport, \count($this->gridClasses->getColumns())),
            'row' => $this->spanParser->effective($geometrie['row'], $viewport, 1),
            'start' => $this->spanParser->effective($geometrie['col-start'], $viewport, 0),
        ];
    }

    /**
     * Die Klassen des Kindes landen am Zell-div, nicht am Element selbst.
     */
    private function cellClasses(ContentModel $kind): string
    {
        $klassen = [];

        foreach (['grid_columns', 'grid_options'] as $feld) {
            foreach (StringUtil::deserialize($kind->$feld, true) as $klasse) {
                if ($klasse) {
                    $klassen[] = (string) $klasse;
                }
            }
        }

        return implode(' ', array_unique($klassen));
    }

    private function headline(ContentModel $kind): string
    {
        $headline = StringUtil::deserialize($kind->headline, true);

        return (string) ($headline['value'] ?? '');
    }

    private function resolveModel(ContentElementReference $reference): ContentModel|null
    {
        $kind = $reference->getContentModel();

        if ($kind instanceof ContentModel) {
            return $kind;
        }

        return $this->framework->getAdapter(ContentModel::class)->findById($kind);
    }
}
