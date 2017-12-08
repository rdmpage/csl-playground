{
    "_id": "_design/cluster",
    "views": {
        "versions": {
            "map": "function (doc) {\n  if (doc.cluster_id) {\n    if (doc.cluster_id != doc._id) {\n      emit(doc.cluster_id, doc._id);\n    }\n  }\n}"
        }
    },
    "language": "javascript"
}