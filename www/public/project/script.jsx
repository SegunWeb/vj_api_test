
nexrender.duplicateStructure = function(comp) {
    // Duplicate the incoming comp
    var comp = comp.duplicate();

    // For each of the layers in the comp, check for subcomps
    for (var i=1; i<=comp.numLayers; i++) {

        var layer = comp.layer(i);
        //Check if layer has a source and that its type is a composition
        if (layer.source && layer.source.typeName == "Composition") {

            // Check if this comp has already been duplicated
            var check = nexrender.checkPreviousComps(layer.source.id);

            if (check == null) {
                // The subcomp hasn't been duplicated before

                // Store the original comp id to remember the correlation
                var sourceID = layer.source.id;
                // Replace the source of the layer, and recursively check in that subcomp for sub-subcomps
                layer.replaceSource(nexrender.duplicateStructure(layer.source), false);
                // Store the new comp id to remember the correlation
                var destID = layer.source.id;
                //Add the correlation to an array
                previousComps[sourceID] = destID;
            } else {
                // Replace the source with the already duplicated comp
                layer.replaceSource(check, false);
            }
        }
    }

    // For the recursion, return the duplicate comp
    return comp;
};

// Checks previous duplications to make sure a comp isn't duplicated twice
nexrender.checkPreviousComps = function (checkID) {
    if (previousComps[checkID]) {
        return nexrender.getItemWithID(previousComps[checkID]);
    }
    return null;
};

// Returns the proect item with the specified ID
nexrender.getItemWithID = function (id) {
    for (var x=1; x<=app.project.numItems; x++) {
        if (app.project.item(x).id == id) {
            return app.project.item(x);
        }
    }
    return null;
};
