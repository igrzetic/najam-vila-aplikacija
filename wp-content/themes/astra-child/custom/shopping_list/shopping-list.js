document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("shopping-list-form");
  if (!form) return;
  const addItemBtn = form.querySelector("#add-item");
  const itemName = form.querySelector("#item-name");
  const itemQty = form.querySelector("#item-quantity");
  const itemPrice = form.querySelector("#item-price");
  const itemsList = form.querySelector("#items-list");
  const totalItems = form.querySelector("#total-items");
  const totalPrice = form.querySelector("#total-price");
  const listNameInput = form.querySelector("#list-name");
  const listDescInput = form.querySelector("#list-description");
  const propertyIdEl = form.querySelector("#property-id");
  const propertySelectEl = form.querySelector("#property-select");
  const propertyIdNewEl = form.querySelector("#property_id"); // new select id
  function getPropertyId() {
    if (propertyIdEl) {
      const v = parseInt(propertyIdEl.value, 10);
      if (!isNaN(v) && v > 0) return v;
    }
    if (propertyIdNewEl) {
      const v = parseInt(propertyIdNewEl.value, 10);
      if (!isNaN(v) && v > 0) return v;
    }
    if (propertySelectEl) {
      const v = parseInt(propertySelectEl.value, 10);
      if (!isNaN(v) && v > 0) return v;
    }
    return 0;
  }

  // Provided by wp_localize_script in shopping-list.php
  const saveUrl = (window.ShoppingListConfig && window.ShoppingListConfig.saveUrl) ||
    "/wp-content/themes/astra-child/custom/shopping_list/save-shopping-list.php";
  const userId = (window.ShoppingListConfig && window.ShoppingListConfig.userId) ||
    window.currentUserId || 0;
  const userRole = (window.ShoppingListConfig && window.ShoppingListConfig.userRole) || null;

  // Debug log of session-provided user data
  try {
    console.log("[ShoppingList] Session user:", { userId, userRole });
  } catch (e) {}

  let items = [];

  function updateTotals() {
    totalItems.textContent = items.length;
    let sum = items.reduce((acc, cur) => acc + cur.qty * cur.price, 0);
    totalPrice.textContent = sum.toFixed(2);
  }

  addItemBtn.addEventListener("click", () => {
    const name = itemName.value.trim();
    const qty = parseInt(itemQty.value);
    const price = parseFloat(itemPrice.value);

    if (!name || isNaN(qty) || isNaN(price)) return;

    const index = items.length;
    const item = { name, qty, price, status: "pending" };
    items.push(item);

    const li = document.createElement("li");
    li.dataset.index = String(index);

    const text = document.createElement("span");
    text.classList.add("item-text");
    text.innerHTML = `${name} <span class="sep">-</span> ${qty} pcs <span class="sep">-</span> ${price.toFixed(2)} €`;
    li.appendChild(text);

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
        items.splice(i, 1);
        // Remove from DOM
        li.remove();
        // Reindex remaining <li> elements
        const lis = itemsList.querySelectorAll("li");
        lis.forEach((node, idx) => {
          node.dataset.index = String(idx);
        });
        // Update totals
        updateTotals();
      }
    });
    li.appendChild(delBtn);

    itemsList.appendChild(li);

    itemName.value = "";
    itemQty.value = "";
    itemPrice.value = "";

    updateTotals();
  });

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (!listNameInput.value.trim()) {
      alert("Please enter a name for the shopping list.");
      return;
    }
    if (items.length === 0) {
      alert("Your shopping list is empty.");
      return;
    }
    try {
      const pid = getPropertyId();
      if (!pid || isNaN(pid) || pid <= 0) {
        alert("Please select a property.");
        return;
      }

      const payload = {
        user_id: isNaN(userId) ? 0 : userId,
        list_name: listNameInput.value.trim(),
        items: items,
        property_id: pid,
      };

      const res = await fetch(saveUrl, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });
      let data = null;
      try {
        data = await res.json();
      } catch (e) {
        // non-JSON response; treat as success if HTTP status is OK
      }
      const isSuccess = (data && (data.success === true || data.status === 'ok')) || res.ok;
      if (isSuccess) {
        try { console.log('[ShoppingList] Email recipient:', data && data.to_email); } catch(e) {}
        const dashUrl = (window.ShoppingListConfig && window.ShoppingListConfig.dashboardUrl) || "/index.php/admin-dashboard/#shopping-list";
        // Unconditionally navigate then force reload to reflect changes
        try {
          if (window.location.href !== dashUrl) {
            window.location.replace(dashUrl);
          }
        } finally {
          // Double-force refresh in case navigation was hash-only
          setTimeout(() => {
            try { window.location.reload(); } catch(_) { try { window.history.go(0); } catch(__) {} }
          }, 50);
        }
      } else {
        console.error(data);
        alert("Saving failed. See console for details.");
      }
    } catch (e) {
      console.error(e);
      alert("Network or server error while saving the list.");
    }
  });
});
