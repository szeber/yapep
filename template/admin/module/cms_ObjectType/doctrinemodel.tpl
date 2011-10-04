{$className}:
    inheritance:
        extends: ObjectData
        type: concrete
    columns:
        id:
            type: integer(20)
            primary: true
    relations:
        ObjectData:
            class: ObjectData
            local: id
            foreign: id
            type: one
