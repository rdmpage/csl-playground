{
    "_id": "_design/housekeeping",
    "views": {
        "no-doi": {
            "reduce": "_sum",
            "map": "function (doc) {\n  var csl = doc.message;\n  if (csl.DOI) {\n  } else {\n    emit(doc._id, 1);\n  }\n}"
        },
        "pmid-no-doi": {
            "reduce": "_sum",
            "map": "function (doc) {\n  var csl = doc.message;\n  if (csl.PMID) {\n    if (csl.DOI) {\n    } else {\n      emit(doc.PMID, 1);\n    }\n  }\n}"
        },
        "ids": {
            "map": "function (doc) {\n  emit(null, doc._id);\n}"
        },
        "no-title": {
            "reduce": "_sum",
            "map": "function (doc) {\n  var csl = doc.message;\n  if (csl.title) {\n  } else {\n    emit(doc._id, 1);\n  }\n}"
        },
        "message-format": {
            "map": "function (doc) {\n  if (doc['message-format']) {\n    emit (doc['message-format'], 1);\n  }\n}",
            "reduce": "_sum"
        }
    },
    "language": "javascript"
}