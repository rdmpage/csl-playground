{
    "_id": "_design/identifier",
    "views": {
        "DOI": {
            "reduce": "_sum",
            "map": "function (doc) {\n  var csl = doc.message;\n  if (csl.DOI) {\n    emit(csl.DOI, 1);\n  }\n}"
        },
        "alternative-id": {
            "reduce": "_sum",
            "map": "function (doc) {\n  var csl = doc.message;\n  if (csl['alternative-id']) {\n    for (var j in csl['alternative-id']) {\n      emit(csl['alternative-id'][j], 1);\n    }\n  }\n}"
        },
        "PMID": {
            "reduce": "_sum",
            "map": "function (doc) {\n  var csl = doc.message;\n  if (csl.PMID) {\n    emit(csl.PMID, 1);\n  }\n}"
        },
        "bhl": {
            "reduce": "_sum",
            "map": "function (doc) {\n  var csl = doc.message;\n  if (csl['alternative-id']) {\n    for (var j in csl['alternative-id']) {\n      if (csl['alternative-id'][j].match(/biodiversitylibrary.org/)) {\n        emit(csl['alternative-id'][j], 1);\n      }\n    }\n  }\n}"
        }
    },
    "language": "javascript"
}