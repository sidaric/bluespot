async function loadMonth() {

    const today = new Date();

    const month =
        today.getFullYear() + "-" +
        String(today.getMonth() + 1).padStart(2, "0");

    try {

        const response = await fetch(
            `/api/time-entries?month=${month}`,
            {
                headers: {
                    "Accept": "application/json"
                },
                credentials: "same-origin"
            }
        );

        const data = await response.json();

        console.log("API RESPONSE:", data);

    } catch (e) {

        console.error("API ERROR", e);

    }

}

loadMonth();
