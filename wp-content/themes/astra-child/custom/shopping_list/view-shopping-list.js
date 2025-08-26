document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("view-shopping-list-form");
    // Scope selectors to this form to avoid collisions with the create form
    const selectEl = form ? form.querySelector("#list-name") : null;
    const viewBtn = form ? form.querySelector("#view-list") : null;
    const updateBtn = form ? form.querySelector("#update-list") : null;
    const itemsList = form ? form.querySelector("#items-list") : null;
    const totalItems = form ? form.querySelector("#total-items") : null;
    const totalPrice = form ? form.querySelector("#total-price") : null;
    try { console.debug("[ViewShoppingList] JS loaded, form found:", !!form); } catch (_) {}
  
    // Local state for currently loaded items and deleted ids
    let currentItems = [];
    let deletedItemIds = [];
  
    // Render helpers
    function updateTotals(items) {
      if (!totalItems || !totalPrice) return;
      totalItems.textContent = items.length;
      const sum = items.reduce((acc, it) => acc + (Number(it.quantity) || 0) * (Number(it.price) || 0), 0);
      totalPrice.textContent = sum.toFixed(2);
    }
  
    function renderItems(items) {
      if (!itemsList) return;
      itemsList.innerHTML = "";
      items.forEach((it, index) => {
        const li = document.createElement("li");
        li.className = it.status === "purchased" ? "completed" : "";
        li.dataset.itemId = String(it.item_id);
        li.dataset.status = it.status;
        li.dataset.index = String(index);

        // Kreiranje checkboxa
        const checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        checkbox.classList.add("item-checkbox");
        checkbox.checked = it.status === "purchased";

        // Kada se klikne checkbox, dodaj ili ukloni klasu "completed" i ažuriraj status u memoriji
        checkbox.addEventListener("change", () => {
          const i = parseInt(li.dataset.index, 10);
          const checked = checkbox.checked;
          if (checked) {
            li.classList.add("completed");
          } else {
            li.classList.remove("completed");
          }
          if (!isNaN(i) && items[i]) {
            items[i].status = checked ? "purchased" : "pending";
          }
        });

        li.appendChild(checkbox);
  
        // Text
        const span = document.createElement("span");
        span.className = "item-text";
        const qty = Number(it.quantity) || 0;
        const price = Number(it.price) || 0;
        span.innerHTML = `${it.item_name} <span class="sep">-</span> ${qty} pcs <span class="sep">-</span> ${price.toFixed(2)} €`;
        li.appendChild(span);

        // Delete button
        const delBtn = document.createElement("button");
        delBtn.type = "button";
        delBtn.className = "delete-btn";
        // delBtn.title = "Remove item";
        delBtn.textContent = "🗑️";
        delBtn.addEventListener("click", () => {
          const i = parseInt(li.dataset.index, 10);
          if (!isNaN(i)) {
            // Remove from data
            const removed = items.splice(i, 1)[0];
            // Track deletion (only if item_id exists)
            const removedId = removed && removed.item_id ? Number(removed.item_id) : 0;
            if (removedId > 0 && !deletedItemIds.includes(removedId)) {
              deletedItemIds.push(removedId);
            }
            // Remove from DOM
            li.remove();
            // Reindex remaining <li> elements
            const lis = itemsList.querySelectorAll("li");
            lis.forEach((node, idx) => {
              node.dataset.index = String(idx);
            });
            // Update totals
            updateTotals(items);
          }
        });
        li.appendChild(delBtn);

        itemsList.appendChild(li);
      });
  
      updateTotals(items);
    }

    async function fetchAndRenderItems(listId) {
      if (!listId) {
        alert("Please select a list.");
        return;
      }
      try {
        const base = (window.ViewShoppingListConfig && window.ViewShoppingListConfig.fetchUrl) || '';
        const url = `${base}?list_id=${encodeURIComponent(listId)}`;
        const res = await fetch(url, { headers: { "Accept": "application/json" } });
        const data = await res.json();
        if (!data || !data.success) {
          console.error(data);
          alert("Failed to load items.");
          return;
        }
        currentItems = data.items || [];
        deletedItemIds = [];
        renderItems(currentItems);
      } catch (e) {
        console.error(e);
        alert("Error while fetching items.");
      }
    }
  
    // Button action: load items for selected list
    if (viewBtn) {
      viewBtn.addEventListener("click", () => {
        const listId = selectEl && selectEl.value ? parseInt(selectEl.value, 10) : 0;
        fetchAndRenderItems(listId);
      });
    }

    // Button action: update current items statuses on server
    if (updateBtn) {
      updateBtn.addEventListener("click", async () => {
        const listId = selectEl && selectEl.value ? parseInt(selectEl.value, 10) : 0;
        if (!listId) {
          alert("Please select a list.");
          return;
        }
        if (!Array.isArray(currentItems) || currentItems.length === 0) {
          alert("There are no items to update.");
          return;
        }
        try {
          const url = (window.ViewShoppingListConfig && window.ViewShoppingListConfig.updateUrl) || '';
          const payload = {
            list_id: listId,
            items: currentItems.map(it => ({ item_id: Number(it.item_id), status: it.status })),
            deleted_item_ids: deletedItemIds
          };
          const res = await fetch(url, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
          });
          const data = await res.json();
          if (data && data.success) {
            alert("List updated successfully.");
            window.location.reload();
          } else {
            console.error(data);
            alert("Update failed.");
          }
        } catch (e) {
          console.error(e);
          alert("Error while updating the list.");
        }
      });
    }
  });