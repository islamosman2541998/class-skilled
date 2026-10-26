// Drag & drop ordering (SortableJS).
// <tbody x-sortable="reorder"> with children carrying data-sort-id; on drop the
// new id order is sent to the Livewire method (`reorder(array $ids)`).
// Optional modifiers: x-sortable.handle uses [data-sort-handle] as handle; x-sortable.scope="reorder"
// calls the enclosing Alpine component's reorder(ids) instead of the Livewire method.
// For nested trees use x-sortable-tree="saveTree" (lists with data-sort-list).
import Sortable from 'sortablejs';

function ids(container) {
    return Array.from(container.children)
        .map((el) => el.dataset.sortId)
        .filter(Boolean);
}

export function registerSortable(Alpine) {
    Alpine.directive('sortable', (el, { expression, modifiers }, { evaluateLater, cleanup }) => {
        // "reorder" → $wire.call('reorder', ids); "sortRepeater('form.items', __ids)" → called as written.
        const target = modifiers.includes('scope')
            ? `${expression}(__ids)`
            : expression.includes('(') ? `$wire.${expression}` : `$wire.call('${expression}', __ids)`;
        const callback = evaluateLater(target);
        const sortable = Sortable.create(el, {
            animation: 150,
            ghostClass: 'a-sort-ghost',
            handle: modifiers.includes('handle') ? '[data-sort-handle]' : undefined,
            onEnd() {
                callback(() => {}, { scope: { __ids: ids(el) } });
            },
        });
        cleanup(() => sortable.destroy());
    });

    // Nested lists (depth 2): each list element has data-sort-list (parent id or "root").
    Alpine.directive('sortable-tree', (el, { expression }, { evaluateLater, cleanup }) => {
        const callback = evaluateLater(`$wire.call('${expression}', __tree)`);
        const instances = [];
        const collect = () => {
            const tree = [];
            el.querySelectorAll('[data-sort-list]').forEach((list) => {
                const parent = list.dataset.sortList === 'root' ? null : list.dataset.sortList;
                Array.from(list.children).forEach((item, index) => {
                    if (item.dataset.sortId) tree.push({ id: item.dataset.sortId, parent, sort: index + 1 });
                });
            });
            return tree;
        };
        el.querySelectorAll('[data-sort-list]').forEach((list) => {
            instances.push(
                Sortable.create(list, {
                    group: 'tree',
                    animation: 150,
                    handle: '[data-sort-handle]',
                    ghostClass: 'a-sort-ghost',
                    fallbackOnBody: true,
                    onMove(event) {
                        // Items that have children cannot move into another item.
                        const dragged = event.dragged;
                        const target = event.to;
                        return !(target.dataset.sortList !== 'root' && dragged.querySelector('[data-sort-list] > [data-sort-id]'));
                    },
                    onEnd() {
                        callback(() => {}, { scope: { __tree: collect() } });
                    },
                }),
            );
        });
        cleanup(() => instances.forEach((s) => s.destroy()));
    });
}
