```
JSON.stringify({
    "bitly_code": window.location.pathname,
    "bitly_phishing": document.querySelector("#main > div > div.info-wrapper--spam") ? 1 : 0,
    "redirect_to": {
        "title": document.querySelector("#main > div > div.marketing-data--wrapper > div:nth-child(1) > div.item-detail--title").innerText,
        "url": document.querySelector("#main > div > div.marketing-data--wrapper > div:nth-child(1) > div:nth-child(3) > a").innerText
    },
    "created": {
        "abs": document.querySelector("#main > div > div.marketing-data--wrapper > div:nth-child(1) > time").innerText.replace("CREATED ", ""),
        "date": document.querySelector("#main > div > div.marketing-data--wrapper > div:nth-child(1) > time").attributes.datetime.value
    },
    "clicks": document.querySelector("#main > div > div.marketing-data--wrapper > div:nth-child(3) > div.info-wrapper--CLICKGRAPH > div.item-detail--click-stats-wrapper > div > div.info-wrapper--header > span.info-wrapper--clicks-text").innerText,
    "referrers": document.querySelector("#main > div > div.marketing-data--wrapper > div:nth-child(3) > div:nth-child(2) > div:nth-child(1)").innerHTML,
    "locations": document.querySelector("#main > div > div.marketing-data--wrapper > div:nth-child(3) > div:nth-child(2) > div:nth-child(2)").innerHTML,
    "timeline": document.querySelector("#main > div > div.marketing-data--wrapper > div:nth-child(3) > div.info-wrapper--CLICKGRAPH > div:nth-child(2) > div.bar-chart--MAIN").innerHTML
})
```