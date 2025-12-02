import java.util.*;

class SlidesAndLadders {
    public static void findTheWinnerOfTheSlidesAndLadders(int numberOfPlayers, String[] playersList,
            String startingPlayer, int giftsCount, int[] giftsPosition, int diceCount, int[] diceValues) {

        // Game board settings
        int boardSize = 30;
        Map<Integer, Integer> slides = Map.of(14, 2, 17, 4, 27, 6); // Sliders (heads -> tails)
        Map<Integer, Integer> ladders = Map.of(4, 21, 1, 14); // Ladders (bases -> tops)
        Set<Integer> gifts = new HashSet<>();
        for (int pos : giftsPosition) {
            gifts.add(pos);
        }

        // Player information
        Map<String, Integer> positions = new HashMap<>();
        Map<String, Integer> slidesUsed = new HashMap<>();
        Map<String, Integer> laddersUsed = new HashMap<>();
        Map<String, Integer> giftsCollected = new HashMap<>();
        Map<String, Boolean> hasStarted = new HashMap<>();

        for (String player : playersList) {
            positions.put(player, 0);
            slidesUsed.put(player, 0);
            laddersUsed.put(player, 0);
            giftsCollected.put(player, 0);
            hasStarted.put(player, false);
        }

        // Find starting player index
        int currentPlayerIdx = 0;
        for (int i = 0; i < numberOfPlayers; i++) {
            if (playersList[i].equals(startingPlayer)) {
                currentPlayerIdx = i;
                break;
            }
        }

        // Process dice rolls
        for (int i = 0; i < diceCount; i++) {
            String currentPlayer = playersList[currentPlayerIdx];
            int diceRoll = diceValues[i];

            if (!hasStarted.get(currentPlayer) && diceRoll != 1) {
                currentPlayerIdx = (currentPlayerIdx + 1) % numberOfPlayers;
                continue;
            }

            hasStarted.put(currentPlayer, true);

            int newPosition = positions.get(currentPlayer) + diceRoll;
            if (newPosition <= boardSize) {
                positions.put(currentPlayer, newPosition);
            }

            // Check if the player lands on a slide
            if (slides.containsKey(positions.get(currentPlayer))) {
                positions.put(currentPlayer, slides.get(positions.get(currentPlayer)));
                slidesUsed.put(currentPlayer, slidesUsed.get(currentPlayer) + 1);
            }

            // Check if the player lands on a ladder
            else if (ladders.containsKey(positions.get(currentPlayer))) {
                positions.put(currentPlayer, ladders.get(positions.get(currentPlayer)));
                laddersUsed.put(currentPlayer, laddersUsed.get(currentPlayer) + 1);
            }

            // Check if the player lands on a gift
            if (gifts.contains(positions.get(currentPlayer))) {
                giftsCollected.put(currentPlayer, giftsCollected.get(currentPlayer) + 1);
                gifts.remove(positions.get(currentPlayer)); // Gift disappears after collection
            }

            // Check for victory
            if (positions.get(currentPlayer) == boardSize) {
                break;
            }

            // Check if two players are in the same position
            for (String player : playersList) {
                if (!player.equals(currentPlayer) && positions.get(player).equals(positions.get(currentPlayer))) {
                    positions.put(player, 0);
                    hasStarted.put(player, false);
                }
            }

            // Extra turn if dice roll is 1 or 5, else switch to next player
            if (diceRoll != 1 && diceRoll != 5) {
                currentPlayerIdx = (currentPlayerIdx + 1) % numberOfPlayers;
            }
        }

        // Display player status
        for (String player : playersList) {
            int position = positions.get(player);
            int remaining = Math.max(0, boardSize - position);
            System.out.println(player + "|" + position + "|" + remaining + "|" +
                    slidesUsed.get(player) + "|" + laddersUsed.get(player) + "|" + giftsCollected.get(player));
        }

        // Determine the winner
        String winner = "";
        for (String player : playersList) {
            if (positions.get(player) == boardSize) {
                winner = player;
                break;
            }
        }
        System.out.println(winner + " is the winner");
    }
}

class Main {
    public static void main(String[] args) {
        Scanner scanner = new Scanner(System.in);

        int numberOfPlayers = scanner.nextInt();
        String[] playersList = new String[numberOfPlayers];
        for (int i = 0; i < numberOfPlayers; i++) {
            playersList[i] = scanner.next();
        }

        String startingPlayer = scanner.next();

        int giftsCount = scanner.nextInt();
        int[] giftsPosition = new int[giftsCount];
        for (int i = 0; i < giftsCount; i++) {
            giftsPosition[i] = scanner.nextInt();
        }

        int diceCount = scanner.nextInt();
        int[] diceValues = new int[diceCount];
        for (int i = 0; i < diceCount; i++) {
            diceValues[i] = scanner.nextInt();
        }

        SlidesAndLadders.findTheWinnerOfTheSlidesAndLadders(numberOfPlayers, playersList, startingPlayer,
                giftsCount, giftsPosition, diceCount, diceValues);

        scanner.close();
    }
}
