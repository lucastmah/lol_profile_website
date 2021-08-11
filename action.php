<html>
<body>

<?php echo htmlspecialchars($_POST["summoner"]); ?>

<script src="searchScript.js">
    searchFunction(htmlspecialchars($_POST["summoner"]));
</script>


</body>
</html>