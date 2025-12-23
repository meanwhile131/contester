window.onload = () => {
    document.getElementById("io").addEventListener("input", e => {
        const table = e.target;
        for (let i = table.rows.length - 1; i > -1; i--) {
            if (Array.from(table.rows[i].cells).every(a => a.textContent == "") && table.rows.length > 1) {
                table.deleteRow(i);
            }
        }
        if (Array.from(table.rows[table.rows.length - 1].cells).every(a => a.textContent != "")) {
            let row = table.insertRow();
            row.insertCell();
            row.insertCell();
        }
    })
};

function edit() {
    let tests = [];
    const table = document.getElementById("io").getElementsByTagName("tbody")[0];
    for (let i = 0; i < table.rows.length - 1; i++) {
        const row = table.rows[i];
        const test = {in: row.cells[0].innerText, out: row.cells[1].textContent};
        tests.push(test);
    }

    document.getElementById("form_text").value = document.getElementById("text").textContent;
    document.getElementById("form_name").value = document.getElementById("name").textContent;
    document.getElementById("form_tests").value = JSON.stringify(tests);
    document.getElementById("send_form").submit();
}