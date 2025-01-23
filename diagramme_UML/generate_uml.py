from graphviz import Digraph

# Création du diagramme UML
uml = Digraph("Ecommerce_UML", format="png")
uml.attr(rankdir="LR", size="8,5")

# Styles généraux
uml.attr("node", shape="record", fontname="Helvetica")

# Définition des classes
uml.node("User", "{User|+ id : int\\l+ name : string\\l+ email : string\\l+ password : string\\l+ delivery_address : string\\l}")
uml.node("Cart", "{Cart|+ id : int\\l+ total_price : float\\l}")
uml.node("Product", "{Product|+ id : int\\l+ name : string\\l+ price : float\\l+ highlighted : bool\\l}")
uml.node("ProductSize", "{ProductSize|+ id : int\\l+ size : string\\l+ stock : int\\l}")
uml.node("Size", "{Size|+ id : int\\l+ name : string\\l}")

# Relations entre les classes
uml.edge("User", "Cart", "1..*")
uml.edge("Cart", "ProductSize", "*..*")
uml.edge("Product", "ProductSize", "1..*")
uml.edge("ProductSize", "Size", "1..1")

# Génération du diagramme
uml_filepath = "/Users/emma1/Desktop/CEF/Stubborn e-commerce/Stubborn/diagramme_UML/Stubborn_Ecommerce_UML"
uml.render(uml_filepath, view=False)

uml_filepath